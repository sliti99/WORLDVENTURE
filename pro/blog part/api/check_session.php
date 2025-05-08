<?php
/**
 * Session Check API
 * Provides information about the current user session status
 * Used for validating user authentication in JavaScript
 */

// Start session
session_start();

// Include auth helpers
require_once __DIR__ . '/../config/auth.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Check if user is logged in
$isLoggedIn = isLoggedIn();

// Return session status
echo json_encode([
    'success' => true,
    'isLoggedIn' => $isLoggedIn,
    'user' => [
        'id' => $_SESSION['user_id'] ?? 0,
        'name' => $_SESSION['name'] ?? 'Guest',
        'role' => getUserRole() ?? 'visitor'
    ],
    'timestamp' => date('Y-m-d H:i:s')
]);
?>