<?php
require_once 'functions.php';
require_once 'admin/stats_helper.php';

// Load admin settings if they exist
$admin_settings = [];
$settings_file = __DIR__ . '/admin/config.json';
if (file_exists($settings_file)) {
    $admin_settings = json_decode(file_get_contents($settings_file), true) ?: [];
}

$ip = $_SERVER['REMOTE_ADDR'];
$country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'XX';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Check if request is from a bot
if (is_bot($userAgent)) {
    addRedirectRecord($ip, $country, $userAgent, $queryString, 'blocked', 'Bot detected');
    clean_response(); // Return clean response for bots
}

// Check country restrictions
$allowed_countries = !empty($admin_settings['allowed_countries']) 
    ? array_map('trim', explode(',', strtoupper($admin_settings['allowed_countries'])))
    : [];
$blocked_countries = !empty($admin_settings['blocked_countries'])
    ? array_map('trim', explode(',', strtoupper($admin_settings['blocked_countries'])))
    : [];

// If allowed countries are specified and current country is not in the list
if (!empty($allowed_countries) && !in_array($country, $allowed_countries)) {
    addRedirectRecord($ip, $country, $userAgent, $queryString, 'blocked', 'Country not allowed');
    http_response_code(403);
    exit("Access denied: Your country is not allowed.");
}

// If country is in blocked list
if (!empty($blocked_countries) && in_array($country, $blocked_countries)) {
    addRedirectRecord($ip, $country, $userAgent, $queryString, 'blocked', 'Country blocked');
    http_response_code(403);
    exit("Access denied: Your country is blocked.");
}

// Check VPN/Tor only if blocking is enabled in admin settings
if (($admin_settings['block_vpn'] ?? true) && isVpnOrTor($ip)) {
    // Log the blocked attempt
    addRedirectRecord($ip, $country, $userAgent, $queryString, 'blocked', 'VPN/Tor detected');
    http_response_code(403);
    exit("Access denied: VPN/Tor connections are not allowed.");
}

// Get all parameters to check
$required_params = $admin_settings['required_params'] ?? ['gclid'];

// Check all required parameters must be present
$missing_required = [];
foreach ($required_params as $param) {
    if (!isset($_GET[$param]) || empty($_GET[$param])) {
        $missing_required[] = $param;
    }
}

if (!empty($missing_required)) {
    $message = sprintf(
        "Missing required parameter(s): %s. Required: %s",
        implode(', ', $missing_required),
        implode(', ', $required_params)
    );
    // Log missing parameters error
    addRedirectRecord($ip, $country, $userAgent, $queryString, 'error', $message);
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(400);
    echo $message;
    exit;
}

// Get URL from query string or settings
$finalUrl = $_GET['url'] ?? $admin_settings['referral_url'] ?? '';

if (empty($finalUrl)) {
    // Log missing URL error
    addRedirectRecord($ip, $country, $userAgent, $queryString, 'error', 'No redirect URL configured');
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(400);
    echo "No redirect URL configured";
    exit;
}

// Log successful redirect
addRedirectRecord($ip, $country, $userAgent, $queryString, 'success', '');

// Redirect to final URL
header("Location: $finalUrl", true, 302);
exit;
?>
