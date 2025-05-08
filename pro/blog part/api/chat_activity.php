<?php
/**
 * Chat Activity API
 * Tracks user presence and new message notifications for the chat system
 */

// Start session
session_start();

// Include required files
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../controllers/ChatC.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Create chat controller instance
$chatC = new ChatC();

// Default response
$response = [
    'success' => false,
    'action' => $_REQUEST['action'] ?? 'unknown',
    'timestamp' => date('Y-m-d H:i:s')
];

// Get action from request
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'ping':
        // Update user's last activity timestamp
        if (isLoggedIn()) {
            // This would typically update a user_activity table in a production system
            $response['success'] = true;
            $response['user'] = [
                'id' => $_SESSION['user_id'] ?? 0,
                'name' => $_SESSION['name'] ?? 'Anonymous',
                'role' => getUserRole() ?? 'visitor'
            ];
        } else {
            $response['error'] = 'Not logged in';
        }
        break;
        
    case 'check_new':
        // Check for new messages since last check
        $lastCheck = $_REQUEST['since'] ?? date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $newCount = $chatC->compterNouveauxMessages($lastCheck);
        
        $response['success'] = true;
        $response['new_messages'] = $newCount;
        $response['since'] = $lastCheck;
        break;
        
    case 'active_users':
        // In a production system, this would return users active in the last few minutes
        // For this implementation, we'll just return a success response
        $response['success'] = true;
        $response['active_users'] = isLoggedIn() ? 1 : 0; // Simplified for demo
        break;
        
    default:
        $response['error'] = 'Unknown action';
}

// Return JSON response
echo json_encode($response);
?>