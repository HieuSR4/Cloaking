<?php
require_once 'stats_helper.php';
require_once 'auth_config.php';

// Check authentication
checkAuth();

// Load all stats
$allStats = loadStats();

// Calculate statistics
$totalVisits = count($allStats);
$blockedVisits = count(array_filter($allStats, fn($r) => $r['status'] === 'blocked'));
$successVisits = count(array_filter($allStats, fn($r) => $r['status'] === 'success'));
$errorVisits = count(array_filter($allStats, fn($r) => $r['status'] === 'error'));

// Get visits by country
$countryStats = [];
foreach ($allStats as $record) {
    $country = $record['country'];
    if (!isset($countryStats[$country])) {
        $countryStats[$country] = 0;
    }
    $countryStats[$country]++;
}
arsort($countryStats); // Sort by visits count

// Get recent visits (last 24 hours)
$last24Hours = array_filter($allStats, function($record) {
    return strtotime($record['timestamp']) > strtotime('-24 hours');
});
$recentVisits = count($last24Hours);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body { padding: 20px; }
        .container { max-width: 1200px; }
        .dashboard-card { box-shadow: 0 0 10px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .dashboard-card:hover { transform: translateY(-5px); }
        .stat-card { border-radius: 10px; }
        .stat-icon { font-size: 2.5rem; opacity: 0.8; }
        .country-list { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Traffic Dashboard</h1>
            <div>
                <a href="stats.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-table"></i> View Details
                </a>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card bg-primary text-white stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Visits</h6>
                                <h2 class="mb-0"><?php echo number_format($totalVisits); ?></h2>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-globe"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-success text-white stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Successful</h6>
                                <h2 class="mb-0"><?php echo number_format($successVisits); ?></h2>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-danger text-white stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Blocked</h6>
                                <h2 class="mb-0"><?php echo number_format($blockedVisits); ?></h2>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-shield-x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card bg-warning text-white stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Last 24h</h6>
                                <h2 class="mb-0"><?php echo number_format($recentVisits); ?></h2>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Country Stats -->
        <div class="row">
            <div class="col-md-12">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Visits by Country</h5>
                    </div>
                    <div class="card-body country-list">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Country</th>
                                        <th>Visits</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($countryStats as $country => $visits): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($country); ?></td>
                                        <td><?php echo number_format($visits); ?></td>
                                        <td>
                                            <?php $percentage = ($visits / $totalVisits) * 100; ?>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo $percentage; ?>%"
                                                     aria-valuenow="<?php echo $percentage; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo number_format($percentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 