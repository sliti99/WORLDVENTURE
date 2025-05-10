<?php
/**
 * Content Validation API
 * This file processes content validation requests from client-side JavaScript
 * and returns validation results using the FilterService
 */

// Enable error reporting in development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the FilterService
require_once __DIR__ . '/../config/FilterService.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verify the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed',
        'clean' => false
    ]);
    exit;
}

// Get the content from the request body
$data = json_decode(file_get_contents('php://input'), true);
$content = isset($data['content']) ? trim($data['content']) : '';

// Check if content is empty
if (empty($content)) {
    echo json_encode([
        'success' => true,
        'clean' => true,
        'message' => 'No content to check'
    ]);
    exit;
}

try {
    // Use the FilterService to check the content
    $result = FilterService::filterContent($content);
    
    // Add success flag
    $result['success'] = true;
    
    // Return the result
    echo json_encode($result);
} catch (Exception $e) {
    // Log the error
    error_log('Content validation error: ' . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'clean' => false,
        'message' => 'Error validating content',
        'error' => $e->getMessage()
    ]);
}
?>