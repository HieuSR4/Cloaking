<?php
require_once 'stats_helper.php';
require_once 'auth_config.php';

// Check authentication
checkAuth();

// Get filters from query string
$filters = [
    'status' => $_GET['status'] ?? '',
    'country' => $_GET['country'] ?? '',
    'search' => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Get current page from query string
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$stats = getStats($page, 20, $filters); // 20 items per page

// Get unique countries for filter dropdown
$countries = getUniqueCountries();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirect Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body { padding: 20px; }
        .container { max-width: 1400px; }
        .stats-card { box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .table-responsive { margin-bottom: 20px; }
        .pagination { justify-content: center; }
        .query-string { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .query-string:hover { overflow: visible; white-space: normal; }
        .status-success { color: #198754; }
        .status-blocked { color: #dc3545; }
        .status-error { color: #ffc107; }
        .filter-form { background: #f8f9fa; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Redirect Statistics</h1>
            <div>
                <a href="dashboard.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-graph-up"></i> Dashboard
                </a>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <form class="filter-form p-3 mb-4" method="GET">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="success" <?php echo $filters['status'] === 'success' ? 'selected' : ''; ?>>Success</option>
                        <option value="blocked" <?php echo $filters['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                        <option value="error" <?php echo $filters['status'] === 'error' ? 'selected' : ''; ?>>Error</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Country</label>
                    <select name="country" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($countries as $country): ?>
                        <option value="<?php echo htmlspecialchars($country); ?>" 
                                <?php echo $filters['country'] === $country ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($country); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search (IP/Query)</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($filters['search']); ?>"
                           placeholder="Search IP or query string...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
            <?php if (!empty($filters['status']) || !empty($filters['country']) || !empty($filters['search']) || 
                      !empty($filters['date_from']) || !empty($filters['date_to'])): ?>
            <div class="mt-2">
                <a href="?" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
            </div>
            <?php endif; ?>
        </form>

        <div class="card stats-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>IP</th>
                                <th>Country</th>
                                <th>Time (VN)</th>
                                <th>OS</th>
                                <th>Status</th>
                                <th>User Agent</th>
                                <th>Query String</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['items'])): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No records found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($stats['items'] as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['id']); ?></td>
                                <td><?php echo htmlspecialchars($record['ip']); ?></td>
                                <td><?php echo htmlspecialchars($record['country']); ?></td>
                                <td><?php echo htmlspecialchars($record['timestamp']); ?></td>
                                <td><?php echo htmlspecialchars($record['os']); ?></td>
                                <td>
                                    <span class="status-<?php echo htmlspecialchars($record['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($record['status'])); ?>
                                        <?php if (!empty($record['reason'])): ?>
                                            <i class="bi bi-info-circle" title="<?php echo htmlspecialchars($record['reason']); ?>"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($record['user_agent']); ?>">
                                        <?php echo htmlspecialchars($record['user_agent']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="query-string" title="<?php echo htmlspecialchars($record['query_string']); ?>">
                                        <?php echo htmlspecialchars($record['query_string']); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($stats['pages'] > 1): ?>
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&<?php echo http_build_query($filters); ?>">Previous</a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($stats['pages'], $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $stats['pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&<?php echo http_build_query($filters); ?>">Next</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
</html> 