<?php
// File path for storing settings
define('SETTINGS_FILE', __DIR__ . '/config.json');

// Function to get settings
function get_settings() {
    if (!file_exists(SETTINGS_FILE)) {
        return [
            'referral_url' => '',
            'block_vpn' => true,
            'required_params' => ['gclid'], // Default required parameter
            'allowed_countries' => '', // Countries allowed to redirect
            'blocked_countries' => '' // Countries blocked from redirect
        ];
    }
    
    $settings = json_decode(file_get_contents(SETTINGS_FILE), true);
    if (!isset($settings['required_params'])) {
        $settings['required_params'] = ['gclid'];
    }
    if (!isset($settings['allowed_countries'])) {
        $settings['allowed_countries'] = '';
    }
    if (!isset($settings['blocked_countries'])) {
        $settings['blocked_countries'] = '';
    }
    return $settings;
}

// Function to save settings
function save_settings($settings) {
    // Ensure required_params is an array and not empty
    if (!isset($settings['required_params']) || !is_array($settings['required_params'])) {
        $settings['required_params'] = ['gclid'];
    } else {
        // Clean empty values and trim whitespace
        $settings['required_params'] = array_filter(array_map('trim', $settings['required_params']));
    }

    // Clean and format country codes
    if (isset($settings['allowed_countries'])) {
        $settings['allowed_countries'] = trim(strtoupper($settings['allowed_countries']));
    }
    if (isset($settings['blocked_countries'])) {
        $settings['blocked_countries'] = trim(strtoupper($settings['blocked_countries']));
    }
    
    return file_put_contents(SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT)) !== false;
}
?> 