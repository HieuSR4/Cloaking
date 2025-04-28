<?php
// File path for storing stats
define('STATS_FILE', __DIR__ . '/data/stats.json');

// Ensure data directory exists
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

// Function to get client OS
function getOS($userAgent) {
    $os_array = [
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/linux/i'             =>  'Linux',
        '/android/i'           =>  'Android',
        '/iphone/i'            =>  'iPhone',
        '/ipad/i'              =>  'iPad',
        '/mobile/i'            =>  'Mobile'
    ];

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $userAgent)) {
            return $value;
        }
    }
    return 'Unknown OS';
}

// Function to load stats
function loadStats() {
    if (!file_exists(STATS_FILE)) {
        return [];
    }
    $data = file_get_contents(STATS_FILE);
    return json_decode($data, true) ?: [];
}

// Function to save stats
function saveStats($stats) {
    file_put_contents(STATS_FILE, json_encode($stats, JSON_PRETTY_PRINT));
}

// Function to add new redirect record
function addRedirectRecord($ip, $country, $userAgent, $queryString, $status = 'success', $reason = '') {
    $stats = loadStats();
    
    // Create new record
    $record = [
        'id' => count($stats) + 1,
        'ip' => $ip,
        'country' => $country,
        'timestamp' => date('Y-m-d H:i:s'), // Vietnam time
        'os' => getOS($userAgent),
        'user_agent' => $userAgent,
        'query_string' => $queryString,
        'status' => $status,
        'reason' => $reason
    ];
    
    // Add to stats array
    array_unshift($stats, $record); // Add to beginning of array
    
    // Keep only last 1000 records to prevent file from getting too large
    $stats = array_slice($stats, 0, 1000);
    
    // Save updated stats
    saveStats($stats);
    
    return $record;
}

// Function to get unique countries from stats
function getUniqueCountries() {
    $stats = loadStats();
    $countries = array_unique(array_column($stats, 'country'));
    sort($countries);
    return $countries;
}

// Function to get stats with filtering and pagination
function getStats($page = 1, $perPage = 20, $filters = []) {
    $stats = loadStats();
    
    // Apply filters
    if (!empty($filters)) {
        $stats = array_filter($stats, function($record) use ($filters) {
            // Status filter
            if (!empty($filters['status']) && $record['status'] !== $filters['status']) {
                return false;
            }
            
            // Country filter
            if (!empty($filters['country']) && $record['country'] !== $filters['country']) {
                return false;
            }
            
            // Search filter (IP or query string)
            if (!empty($filters['search'])) {
                $search = strtolower($filters['search']);
                $ip = strtolower($record['ip']);
                $query = strtolower($record['query_string']);
                
                if (strpos($ip, $search) === false && strpos($query, $search) === false) {
                    return false;
                }
            }
            
            // Date range filter
            if (!empty($filters['date_from'])) {
                $recordDate = strtotime($record['timestamp']);
                $fromDate = strtotime($filters['date_from']);
                if ($recordDate < $fromDate) {
                    return false;
                }
            }
            
            if (!empty($filters['date_to'])) {
                $recordDate = strtotime($record['timestamp']);
                $toDate = strtotime($filters['date_to'] . ' 23:59:59');
                if ($recordDate > $toDate) {
                    return false;
                }
            }
            
            return true;
        });
    }
    
    $total = count($stats);
    $start = ($page - 1) * $perPage;
    $items = array_slice($stats, $start, $perPage);
    
    return [
        'items' => $items,
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'current_page' => $page
    ];
}
?> 