<?php
require_once '../functions.php';
require_once 'settings.php';
require_once 'auth_config.php';

// Check authentication
checkAuth();

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get required parameters from form
        $required_params = isset($_POST['required_params']) ? $_POST['required_params'] : '';
        $required_array = array_filter(array_map('trim', explode(',', $required_params)));

        // Get minimum required parameters
        $min_required = isset($_POST['min_required']) ? intval($_POST['min_required']) : 1;
        
        $new_settings = [
            'referral_url' => trim($_POST['referral_url'] ?? ''),
            'block_vpn' => isset($_POST['block_vpn']),
            'required_params' => $required_array,
            'min_required' => $min_required,
            'allowed_countries' => trim($_POST['allowed_countries'] ?? ''),
            'blocked_countries' => trim($_POST['blocked_countries'] ?? '')
        ];
        
        if (save_settings($new_settings)) {
            $message = "Settings saved successfully!";
            $message_type = "success";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Get current settings
$current_settings = get_settings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body { padding: 20px; }
        .container { max-width: 800px; }
        .settings-card { box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .param-tag { display: inline-block; padding: 2px 8px; margin: 2px; background: #e9ecef; border-radius: 4px; }
        .param-tag.required { background: #dc3545; color: white; }
        .param-tag.optional { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Settings</h1>
            <div>
                <a href="dashboard.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-graph-up"></i> Dashboard
                </a>
                <a href="stats.php" class="btn btn-outline-primary">
                    <i class="bi bi-table"></i> View Details
                </a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" class="card p-4 settings-card">
            <div class="mb-3">
                <label for="referral_url" class="form-label">Referral URL</label>
                <input type="text" class="form-control" id="referral_url" name="referral_url" 
                       value="<?php echo htmlspecialchars($current_settings['referral_url']); ?>"
                       pattern="^$|https?:\/\/.+" title="Leave empty or enter a valid URL starting with http:// or https://">
                <div class="form-text">
                    Enter the complete URL including http:// or https://<br>
                    Leave empty if you don't want to use a referral URL
                </div>
            </div>

            <div class="mb-3">
                <label for="required_params" class="form-label">Required URL Parameters</label>
                <input type="text" class="form-control" id="required_params" name="required_params" 
                       value="<?php echo htmlspecialchars(implode(', ', $current_settings['required_params'])); ?>"
                       placeholder="gclid">
                <div class="form-text">
                    Enter parameter names separated by commas. These are the main parameters to check for.<br>
                    Current required parameters: 
                    <?php foreach ($current_settings['required_params'] as $param): ?>
                        <span class="param-tag required"><?php echo htmlspecialchars($param); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="allowed_countries" class="form-label">Allowed Countries</label>
                <input type="text" class="form-control" id="allowed_countries" name="allowed_countries" 
                       value="<?php echo htmlspecialchars($current_settings['allowed_countries']); ?>"
                       placeholder="VN, US, JP">
                <div class="form-text">
                    Enter country codes separated by commas (e.g., VN, US). Leave empty to allow all countries.<br>
                    Only visitors from these countries will be redirected.
                </div>
            </div>

            <div class="mb-3">
                <label for="blocked_countries" class="form-label">Blocked Countries</label>
                <input type="text" class="form-control" id="blocked_countries" name="blocked_countries" 
                       value="<?php echo htmlspecialchars($current_settings['blocked_countries']); ?>"
                       placeholder="ID, CN">
                <div class="form-text">
                    Enter country codes separated by commas (e.g., ID, CN). Leave empty to block no countries.<br>
                    Visitors from these countries will not be redirected.
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="block_vpn" name="block_vpn" 
                           <?php echo $current_settings['block_vpn'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="block_vpn">Block VPN/Tor Connections</label>
                </div>
                <div class="form-text">When enabled, users using VPN or Tor will be blocked from accessing the site.</div>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 