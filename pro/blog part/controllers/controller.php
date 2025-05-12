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
            $_SESSION['user_id'] = 0; // Default visitor ID
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

    // Detects any banned words in text (case-insensitive, whole words)
    private function containsProfanity(string $text): bool {
        // Define your banned words here
        $bannedWords = ['badword1', 'badword2', 'anotherbadword'];
        foreach ($bannedWords as $word) {
            if (preg_match('/\b'.preg_quote($word,'/').'\b/i', $text)) {
                return true;
            }
        }
        return false;
    }

    // Main request handler
    public function handleRequest() {
        // Clear any previous output and start fresh buffer
        while (ob_get_level()) ob_end_clean();
        
        // Check if request is AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if ($isAjax) {
            header('Content-Type: application/json');
        }
        
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
        $requestData = [];

        // Handle JSON request data for AJAX POST
        if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    $posts = $this->model->getAllPosts();
                    $comments = $this->model->getAllComments();
                    if ($posts === false) {
                        throw new Exception("Failed to fetch posts");
                    }
                    echo json_encode(['posts' => $posts, 'comments' => $comments], JSON_THROW_ON_ERROR);
                    exit;
                } else {
                    return [
                        'posts' => $this->model->getAllPosts(),
                        'comments' => $this->model->getAllComments()
                    ];
                }
                break;

            case 'create': // Handles both AJAX and form submission
                // Handle file upload if present
                $photoPath = null;
                if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = UPLOAD_DIR;
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $tmpName = $_FILES['photo']['tmp_name'];
                    $filename = uniqid('photo_') . '_' . basename($_FILES['photo']['name']);
                    $target = $uploadDir . $filename;
                    if (move_uploaded_file($tmpName, $target)) {
                        $photoPath = UPLOAD_URL . $filename;
                    }
                }
                // Retrieve location data if provided
                $latitude = isset($_POST['latitude']) ? $this->sanitizeInput($_POST['latitude']) : null;
                $longitude = isset($_POST['longitude']) ? $this->sanitizeInput($_POST['longitude']) : null;
                $title = $requestData['title'];
                $content = $requestData['content'];
                
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

                // Profanity check for title/content
                if ($this->containsProfanity($title) || $this->containsProfanity($content)) {
                    if ($isAjax) {
                        echo json_encode(['error' => 'Inappropriate content detected']);
                        exit;
                    }
                    die('Inappropriate content detected in your post.');
                }

                $postId = $this->model->createPost($title, $content, $_SESSION['user_id'], $photoPath, $latitude, $longitude);
                
                if ($isAjax) {
                    echo json_encode([
                        'success' => true,
                        'post' => $this->model->getPostById($postId)
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
                    if ($isAjax) { echo json_encode(['error' => 'Post ID and comment content are required']); exit; }
                    die('Post ID and comment content are required.');
                }
                
                // Basic validation
                if (strlen($content) < 3) {
                    if ($isAjax) { echo json_encode(['error' => 'Comment must be at least 3 characters long']); exit; }
                    die('Comment must be at least 3 characters long.');
                }
                
                // Profanity check for comment content
                if ($this->containsProfanity($content)) {
                    if ($isAjax) {
                        echo json_encode(['error' => 'Inappropriate content detected']);
                        exit;
                    }
                    die('Inappropriate content detected in your comment.');
                }

                // AJAX comment submission
                if ($isAjax) {
                    $commentId = $this->model->addComment($postId, $content);
                    // Fetch the added comment
                    $newComment = array_filter($this->model->getCommentsByPostId($postId), fn($c)=>$c['id']==$commentId);
                    $comment = $newComment ? array_shift($newComment) : ['id'=>$commentId,'post_id'=>$postId,'content'=>$content,'author_name'=>$_SESSION['name']];
                    echo json_encode(['success'=>true,'comment'=>$comment]);
                    exit;
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
                 
                 try {
                     $count = $this->model->toggleReaction($postId, $_SESSION['user_id']);
                     // Check if user has already reacted to highlight the button
                     $hasReacted = $this->model->hasUserReacted($postId, $_SESSION['user_id'], 'post');
                     echo json_encode([
                         'success' => true, 
                         'count' => $count,
                         'hasReacted' => $hasReacted
                     ]);
                 } catch (Exception $e) {
                     echo json_encode(['error' => $e->getMessage()]);
                 }
                 exit;
                 break;
                 
            case 'reactToComment': // Handles AJAX comment reaction toggle
                if (!$isAjax) die('Invalid request method for comment reaction.');
                
                $commentId = $requestData['commentId'] ?? null;
                if (!$commentId) {
                    echo json_encode(['error' => 'Comment ID is required for reaction']);
                    exit;
                }
                
                try {
                    $count = $this->model->toggleCommentReaction($commentId, $_SESSION['user_id']);
                    echo json_encode(['success' => true, 'count' => $count]);
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
                exit;
                break;

            case 'hasReacted': // Check if user has already reacted
                if (!$isAjax) die('Invalid request method.');
                
                $type = $requestData['type'] ?? 'post';
                $itemId = $requestData['itemId'] ?? null;
                
                if (!$itemId) {
                    echo json_encode(['error' => 'Item ID is required']);
                    exit;
                }
                
                $hasReacted = $this->model->hasUserReacted($itemId, $_SESSION['user_id'], $type);
                echo json_encode(['hasReacted' => $hasReacted]);
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

    // Get latest posts (limit parameter for controlling number of posts returned)
    public function getLatestPosts($limit = 3) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view posts.');
        }
        return $this->model->getLatestPosts($limit);
    }

    // Public method to add a comment (callable directly from views)
    public function addComment($postId, $content) {
        // Check permissions
        if (!$this->checkPermission('comment')) {
            throw new Exception('Permission denied: You must be logged in to comment.');
        }
        
        // Basic validation
        if (strlen($content) < 3) {
            throw new Exception('Comment must be at least 3 characters long.');
        }
        
        return $this->model->addComment($postId, $content);
    }

    // Check if a user has reacted to a specific item
    public function hasUserReacted($itemId, $userId, $type = 'post') {
        if (!$this->checkPermission('view')) {
            return false;
        }
        
        if (!$userId || $userId === 0) {
            return false;
        }
        
        return $this->model->hasUserReacted($itemId, $userId, $type);
    }

    // Helper for input sanitization (make it public so views can use it)
    public function sanitizeInput($data) {
        return htmlspecialchars(trim($data ?? ''));
    }
}
?>