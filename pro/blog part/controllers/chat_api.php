<?php
// Suppress PHP errors from being output - essential for JSON APIs
ini_set('display_errors', 0);

// Ensure all errors are captured in error_log
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent any unwanted output
ob_start();

require_once __DIR__ . '/ChatC.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/FilterService.php';

// Ensure proper session management
session_start();

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    // Create an instance of the ChatController
    $chatC = new ChatC();

    // Handle different API actions
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    switch ($action) {
        case 'get_messages':
            // Anyone can view messages, but we'll only return 50 most recent
            try {
                $messages = $chatC->recupererMessages(50);
                echo json_encode([
                    'success' => true,
                    'messages' => $messages
                ]);
            } catch (Exception $e) {
                error_log("Error retrieving chat messages: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Error retrieving chat messages'
                ]);
            }
            break;
            
        case 'send_message':
            // Only logged-in users can send messages
            if (!isLoggedIn()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You must be logged in to send messages'
                ]);
                exit;
            }
            
            // Get the message content
            $content = isset($_POST['message']) ? trim($_POST['message']) : '';
            
            // Validate the message
            if (empty($content)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Message cannot be empty'
                ]);
                exit;
            }
            
            // Use enhanced profanity filtering with FilterService
            $filterResult = FilterService::filterContent($content);
            if (!$filterResult['clean']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Your message was blocked due to inappropriate content'
                ]);
                exit;
            }
            
            // All checks passed, add the message with proper session validation
            try {
                // Ensure session variables exist and have default values if not set
                $userId = $_SESSION['user_id'] ?? 0;
                $userName = $_SESSION['name'] ?? 'Anonymous';
                $userRole = getUserRole() ?? 'visitor';
                
                // Log session data for debugging
                error_log("Chat message from user ID: $userId, Name: $userName, Role: $userRole");
                
                $messageId = $chatC->ajouterMessage($userId, $content, $userName, $userRole);
                
                if (!$messageId) {
                    throw new Exception("Failed to add message to database");
                }
                
                // Return the message with all details
                $message = $chatC->recupererMessageById($messageId);
                
                echo json_encode([
                    'success' => true,
                    'message' => $message
                ]);
            } catch (Exception $e) {
                error_log("Error sending message: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Error sending message'
                ]);
            }
            break;
            
        case 'check_session':
            // Endpoint for checking if user session is valid
            echo json_encode([
                'success' => true,
                'isLoggedIn' => isLoggedIn(),
                'userId' => $_SESSION['user_id'] ?? 0,
                'userName' => $_SESSION['name'] ?? '',
                'userRole' => getUserRole() ?? 'visitor'
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    // Log the error
    error_log("Chat API error: " . $e->getMessage());
    
    // Ensure we return valid JSON even when there's an unexpected error
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred',
        'error' => $e->getMessage()
    ]);
}

// Clean any buffered output before ending
ob_end_flush();
?>