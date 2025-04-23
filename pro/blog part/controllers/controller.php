<?php
require_once '../models/model.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class BlogController {
    private $model;

    public function __construct() {
        $this->model = new BlogModel();
        // Ensure default role/user ID if not set (for testing)
        if (!isset($_SESSION['role'])) {
            $_SESSION['role'] = 'visitor';
            $_SESSION['user_id'] = 1; // Default visitor/test user ID
        }
    }

    // Check user permissions based on role
    private function checkPermission($action) {
        $role = $_SESSION['role'] ?? 'visitor';
        
        $permissions = [
            'visitor' => ['view', 'list'], // Can only view/list
            'user' => ['view', 'list', 'create', 'react', 'comment', 'addComment'], // Can create/react/comment
            'admin' => ['view', 'list', 'create', 'react', 'comment', 'addComment', 'delete', 'edit'] // Full CRUD
        ];
        
        // Allow 'switchRole' for testing purposes regardless of current role
        if ($action === 'switchRole') return true;
        
        return in_array($action, $permissions[$role] ?? []);
    }

    // Main request handler
    public function handleRequest() {
        // Check if request is AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
        $requestData = [];

        // Handle JSON request data for AJAX POST
        if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // Use JSON data if available, otherwise fallback to POST
            $requestData = $data ?? $_POST;
            $action = $requestData['action'] ?? $action; // Get action from JSON data if present
        }
        // Handle standard form POST or GET requests
        else {
            $requestData = $_POST + $_GET; // Combine POST and GET
        }

        // Permission Check
        if (!$this->checkPermission($action)) {
            if ($isAjax) {
                echo json_encode(['error' => 'Permission denied for action: ' . $action]);
                exit;
            }
            die('Permission denied');
        }

        // Route based on action
        switch ($action) {
            case 'list':
                if ($isAjax) {
                    echo json_encode(['posts' => $this->model->getAllPosts()]);
                    exit;
                } else {
                    // For non-AJAX, return data for template rendering (e.g., backend)
                    return [
                        'posts' => $this->model->getAllPosts(),
                        'comments' => $this->model->getAllComments()
                    ];
                }
                break;

            case 'create': // Handles both AJAX and form submission
                $title = $this->sanitizeInput($requestData['title'] ?? '');
                $content = $this->sanitizeInput($requestData['content'] ?? '');

                if (empty($title) || empty($content)) {
                    if ($isAjax) {
                        echo json_encode(['error' => 'Title and content are required']);
                        exit;
                    }
                    die('Title and content are required.');
                }
                
                // Basic validation (can be expanded)
                if (strlen($content) < 10) {
                     if ($isAjax) {
                        echo json_encode(['error' => 'Content must be at least 10 characters long.']);
                        exit;
                    }
                    die('Content must be at least 10 characters long.');
                }

                $postId = $this->model->createPost($title, $content);
                
                if ($isAjax) {
                    echo json_encode([
                        'success' => true,
                        'post' => $this->model->getPostById($postId) // Return the created post
                    ]);
                    exit;
                } else {
                    // Redirect for standard form submission
                    header('Location: blog_backend.php?success=created');
                    exit;
                }
                break;

            case 'delete': // Handles both AJAX and GET requests
                $id = $requestData['id'] ?? null;
                if (!$id) {
                    if ($isAjax) {
                        echo json_encode(['error' => 'Post ID is required']);
                        exit;
                    }
                    die('Post ID is required.');
                }
                
                // Add extra check: Ensure only admin can delete
                if ($_SESSION['role'] !== 'admin') {
                     if ($isAjax) {
                        echo json_encode(['error' => 'Only admins can delete posts.']);
                        exit;
                    }
                    die('Permission denied: Only admins can delete posts.');
                }

                $this->model->deletePost($id);
                
                if ($isAjax) {
                    echo json_encode(['success' => true]);
                    exit;
                } else {
                    // Redirect for standard GET request deletion
                    header('Location: blog_backend.php?deleted=true');
                    exit;
                }
                break;

            case 'addComment': // Handles standard form submission from post_details.php
                $postId = $requestData['post_id'] ?? null;
                $content = $this->sanitizeInput($requestData['comment'] ?? '');

                if (!$postId || !$content) {
                    // Handle error appropriately, maybe redirect back with error message
                    die('Post ID and comment content are required.');
                }
                
                // Basic validation
                 if (strlen($content) < 3) {
                    // Redirect back with error? For now, just die.
                    die('Comment must be at least 3 characters long.');
                }

                $this->model->addComment($postId, $content);
                header("Location: ../views/post_details.php?id=$postId&comment_success=true");
                exit;
                break;
                
            case 'react': // Handles AJAX reaction toggle
                 if (!$isAjax) die('Invalid request method for reaction.');
                 
                 $postId = $requestData['postId'] ?? null;
                 if (!$postId) {
                     echo json_encode(['error' => 'Post ID is required for reaction']);
                     exit;
                 }
                 
                 $count = $this->model->toggleReaction($postId, $_SESSION['user_id']);
                 echo json_encode(['success' => true, 'count' => $count]);
                 exit;
                 break;
                 
            case 'switchRole': // Handles AJAX role switching for testing
                if (!$isAjax) die('Invalid request method for role switch.');
                
                $newRole = $requestData['role'] ?? 'visitor';
                if (in_array($newRole, ['visitor', 'user', 'admin'])) {
                    $_SESSION['role'] = $newRole;
                    // Assign a default user ID based on role for testing
                    $_SESSION['user_id'] = ($newRole === 'admin') ? 1 : (($newRole === 'user') ? 2 : 0);
                    echo json_encode(['success' => true, 'newRole' => $newRole, 'userId' => $_SESSION['user_id']]);
                } else {
                    echo json_encode(['error' => 'Invalid role specified']);
                }
                exit;
                break;

            default:
                // Default action or error handling
                if ($isAjax) {
                    echo json_encode(['error' => 'Unknown action: ' . $action]);
                    exit;
                }
                // For non-AJAX, maybe return default data or show an error page
                return [
                    'posts' => $this->model->getAllPosts(),
                    'comments' => $this->model->getAllComments()
                ];
        }
    }

    // Get single post by ID (used by post_details.php)
    public function getPostById($id) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view post.');
        }
        return $this->model->getPostById($id);
    }
    
    // Get comments for a specific post (used by post_details.php)
    public function getComments($postId) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view comments.');
        }
        return $this->model->getCommentsByPostId($postId);
    }

    // Helper for input sanitization
    private function sanitizeInput($data) {
        return htmlspecialchars(trim($data ?? ''));
    }
}
?>