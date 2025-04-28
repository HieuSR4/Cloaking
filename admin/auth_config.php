<?php
// Authentication configuration
$username = "admin";
$password = "Cotiencoquyen123###"; // Change this to a secure password in production

// Common authentication function
function checkAuth() {
    global $username, $password;
    
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
        $_SERVER['PHP_AUTH_USER'] != $username || $_SERVER['PHP_AUTH_PW'] != $password) {
        header('WWW-Authenticate: Basic realm="Admin Area"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Access Denied';
        exit;
    }
}
?> 