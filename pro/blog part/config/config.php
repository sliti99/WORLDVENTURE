<?php
// Ensure clean output
if (ob_get_level()) ob_end_clean();

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'worldventure');
define('DB_USER', 'root');
define('DB_PASS', '');

// Error handling settings
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Start output buffering
ob_start();

// Session settings
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Default role settings - for testing purposes
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'visitor'; // Options: 'visitor', 'user', 'admin'
    $_SESSION['user_id'] = 0; // Default visitor ID
}

// Base URL
define('BASE_URL', '/web.pro/inst/pro/pro/');

// Path constants
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . BASE_URL);
define('BLOG_PATH', ROOT_PATH . 'blog part/');
define('VIEWS_PATH', BLOG_PATH . 'views/');
define('MODEL_PATH', BLOG_PATH . 'models/');
define('CONTROLLER_PATH', BLOG_PATH . 'controllers/');