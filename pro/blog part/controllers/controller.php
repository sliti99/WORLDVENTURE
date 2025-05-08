<?php
require_once '../config/config.php';
require_once 'PostC.php';
require_once 'CommentC.php';

class BlogController {
    private $model;
    private $postController;
    private $commentController;

    public function __construct() {
        $this->model = new BlogModel();
        $this->postController = new PostC();
        $this->commentController = new CommentC();
        
        // Ensure default role/user ID if not set
        if (!isset($_SESSION['role'])) {
            $_SESSION['role'] = 'visitor';
            $_SESSION['user_id'] = 0; // Default visitor ID
        }
    }

    // PERMISSION HANDLING
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

    // CONTENT PROFANITY CHECK
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

    // Handle file uploads with better path handling
    private function handleFileUpload() {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            error_log("No file uploaded");
            return null;
        }

        try {
            // Define upload directory constants if not already defined
            if (!defined('UPLOAD_DIR')) {
                define('UPLOAD_DIR', '../../uploads/photos/');
                define('UPLOAD_URL', '../../uploads/photos/');
            }

            // Get the uploaded file details
            $file = $_FILES['photo'];
            $fileName = $file['name'];
            $fileTmpPath = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            
            // Check for any upload errors
            if ($fileError !== UPLOAD_ERR_OK) {
                error_log("File upload error: " . $fileError);
                return null;
            }
            
            // Make sure the upload directory exists
            if (!file_exists(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            // Generate a unique filename to prevent overwriting
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = 'photo_' . uniqid() . '_' . $fileName;
            $uploadPath = UPLOAD_DIR . $uniqueFileName;
            
            // Move the uploaded file to the destination
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                // Return the relative URL path to the uploaded file
                error_log("File uploaded successfully to: " . $uploadPath);
                return UPLOAD_URL . $uniqueFileName;
            } else {
                error_log("Failed to move uploaded file to: " . $uploadPath);
                return null;
            }
        } catch (Exception $e) {
            error_log("File upload exception: " . $e->getMessage());
            return null;
        }
    }

    // Process post creation/update with file handling
    private function processPostForm($action = 'create', $id = null) {
        $result = ['success' => false];
        
        // Get form data
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        
        // Check for empty fields
        if (empty($title) || empty($content)) {
            $result['error'] = 'Title and content are required.';
            return $result;
        }
        
        // Check content for profanity
        if ($this->containsProfanity($title) || $this->containsProfanity($content)) {
            $result['error'] = 'Your post contains inappropriate content.';
            return $result;
        }
        
        try {
            // Handle file upload
            $photoPath = $this->handleFileUpload();
            
            // Get location data if provided
            $latitude = $_POST['latitude'] ?? null;
            $longitude = $_POST['longitude'] ?? null;
            
            // Process based on action type
            if ($action === 'create') {
                // Check permission
                if (!$this->checkPermission('create')) {
                    $result['error'] = 'You don\'t have permission to create posts.';
                    return $result;
                }
                
                // Create the post
                $postId = $this->postController->ajouter(
                    $title,
                    $content,
                    $_SESSION['user_id'] ?? 1,
                    $photoPath,
                    $latitude,
                    $longitude
                );
                
                if ($postId) {
                    $result['success'] = true;
                    $result['id'] = $postId;
                    $result['message'] = 'Post created successfully.';
                    
                    // Log successful post creation
                    error_log("Post created with ID: $postId, Photo: " . ($photoPath ?? 'none') . ", Location: $latitude, $longitude");
                } else {
                    $result['error'] = 'Failed to create post.';
                }
            } elseif ($action === 'update' && $id) {
                // Check permission
                if (!$this->checkPermission('update')) {
                    $result['error'] = 'You don\'t have permission to update posts.';
                    return $result;
                }
                
                // Update the post
                $success = $this->postController->modifier(
                    $id,
                    $title,
                    $content,
                    $photoPath,
                    $latitude,
                    $longitude
                );
                
                if ($success) {
                    $result['success'] = true;
                    $result['message'] = 'Post updated successfully.';
                } else {
                    $result['error'] = 'Failed to update post.';
                }
            }
        } catch (Exception $e) {
            error_log("Error processing post: " . $e->getMessage());
            $result['error'] = 'An error occurred while processing your post.';
        }
        
        return $result;
    }

    // MAIN REQUEST HANDLER
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
                    // Using PostC controller
                    $posts = $this->postController->afficherPosts();
                    $this->model->setPosts($posts);
                    
                    // Using CommentC controller
                    $comments = $this->commentController->afficherComments();
                    $this->model->setComments($comments);
                    
                    // Convert models to array for JSON response
                    $postsArray = array_map(function($post) {
                        return [
                            'id' => $post->getId(),
                            'title' => $post->getTitle(),
                            'content' => $post->getContent(),
                            'author_id' => $post->getAuthorId(),
                            'photo_path' => $post->getPhotoPath(),
                            'latitude' => $post->getLatitude(),
                            'longitude' => $post->getLongitude(),
                            'created_at' => $post->getCreatedAt(),
                            'reactions' => $post->getReactions()
                        ];
                    }, $this->model->getPosts());
                    
                    $commentsArray = array_map(function($comment) {
                        return [
                            'id' => $comment->getId(),
                            'post_id' => $comment->getPostId(),
                            'content' => $comment->getContent(),
                            'user_id' => $comment->getUserId(),
                            'created_at' => $comment->getCreatedAt(),
                            'author_name' => $comment->getAuthorName(),
                            'reactions' => $comment->getReactions()
                        ];
                    }, $this->model->getComments());
                    
                    echo json_encode(['posts' => $postsArray, 'comments' => $commentsArray], JSON_THROW_ON_ERROR);
                    exit;
                } else {
                    // Non-AJAX response - prepare data for view
                    $posts = $this->postController->afficherPosts();
                    $this->model->setPosts($posts);
                    
                    // Get comments
                    $comments = $this->commentController->afficherComments();
                    $this->model->setComments($comments);
                    
                    // Convert to arrays for the view
                    $postsArray = array_map(function($post) {
                        return [
                            'id' => $post->getId(),
                            'title' => $post->getTitle(),
                            'content' => $post->getContent(),
                            'author_id' => $post->getAuthorId(),
                            'photo_path' => $post->getPhotoPath(),
                            'latitude' => $post->getLatitude(),
                            'longitude' => $post->getLongitude(),
                            'created_at' => $post->getCreatedAt(),
                            'reactions' => $post->getReactions()
                        ];
                    }, $this->model->getPosts());
                    
                    $commentsArray = array_map(function($comment) {
                        return [
                            'id' => $comment->getId(),
                            'post_id' => $comment->getPostId(),
                            'content' => $comment->getContent(),
                            'user_id' => $comment->getUserId(),
                            'created_at' => $comment->getCreatedAt(),
                            'author_name' => $comment->getAuthorName(),
                            'reactions' => $comment->getReactions()
                        ];
                    }, $this->model->getComments());
                    
                    return ['posts' => $postsArray, 'comments' => $commentsArray];
                }

            case 'create':
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

                // Using PostC controller to add post
                $photoPath = $requestData['photo_path'] ?? null;
                $latitude = $requestData['latitude'] ?? null;
                $longitude = $requestData['longitude'] ?? null;
                
                $postId = $this->postController->ajouter(
                    $title, 
                    $content, 
                    $_SESSION['user_id'], 
                    $photoPath,
                    $latitude,
                    $longitude
                );
                
                if ($isAjax) {
                    $post = $this->postController->detail($postId);
                    echo json_encode([
                        'success' => true,
                        'post' => $post
                    ]);
                    exit;
                } else {
                    // Redirect for standard form submission
                    header('Location: blog_backend.php?success=created');
                    exit;
                }

            case 'delete':
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

                // Use PostC controller to delete post
                $deleted = $this->postController->supprimer($id);
                
                if ($isAjax) {
                    echo json_encode(['success' => $deleted]);
                    exit;
                } else {
                    // Redirect for standard GET request deletion
                    header('Location: blog_backend.php?deleted=true');
                    exit;
                }

            case 'addComment':
                $postId = $requestData['post_id'] ?? null;
                $content = $this->sanitizeInput($requestData['comment'] ?? '');

                if (!$postId || !$content) {
                    if ($isAjax) { 
                        echo json_encode(['error' => 'Post ID and comment content are required']); 
                        exit; 
                    }
                    die('Post ID and comment content are required.');
                }
                
                // Basic validation
                if (strlen($content) < 3) {
                    if ($isAjax) { 
                        echo json_encode(['error' => 'Comment must be at least 3 characters long']); 
                        exit; 
                    }
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

                // Use CommentC controller to add comment
                $commentId = $this->commentController->ajouter($postId, $content, $_SESSION['user_id']);
                
                // AJAX comment submission
                if ($isAjax) {
                    $comment = $this->commentController->detail($commentId);
                    echo json_encode(['success' => true, 'comment' => $comment]);
                    exit;
                }

                header("Location: ../views/post_details.php?id=$postId&comment_success=true");
                exit;

            case 'react':
                if (!$isAjax) die('Invalid request method for reaction.');
                 
                $postId = $requestData['postId'] ?? null;
                if (!$postId) {
                    echo json_encode(['error' => 'Post ID is required for reaction']);
                    exit;
                }
                
                // Use PostC controller for reaction
                $count = $this->postController->handleReaction($postId, $_SESSION['user_id']);
                $hasReacted = $this->postController->hasUserReacted($postId, $_SESSION['user_id']);
                
                echo json_encode(['success' => true, 'count' => $count, 'hasReacted' => $hasReacted]);
                exit;

            case 'reactToComment':
                if (!$isAjax) die('Invalid request method for comment reaction.');
                
                $commentId = $requestData['commentId'] ?? null;
                if (!$commentId) {
                    echo json_encode(['error' => 'Comment ID is required for reaction']);
                    exit;
                }
                
                // Use CommentC controller for comment reaction
                $count = $this->commentController->handleReaction($commentId, $_SESSION['user_id']);
                
                echo json_encode(['success' => true, 'count' => $count]);
                exit;

            case 'hasReacted':
                if (!$isAjax) die('Invalid request method.');
                
                $type = $requestData['type'] ?? 'post';
                $itemId = $requestData['itemId'] ?? null;
                
                if (!$itemId) {
                    echo json_encode(['error' => 'Item ID is required']);
                    exit;
                }
                
                // Use appropriate controller based on type
                if ($type === 'post') {
                    $hasReacted = $this->postController->hasUserReacted($itemId, $_SESSION['user_id']);
                } else {
                    $hasReacted = $this->commentController->hasUserReacted($itemId, $_SESSION['user_id']);
                }
                
                echo json_encode(['hasReacted' => $hasReacted]);
                exit;

            case 'switchRole':
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

            default:
                // Default action or error handling
                if ($isAjax) {
                    echo json_encode(['error' => 'Unknown action: ' . $action]);
                    exit;
                }
                // For non-AJAX, return default data
                return $this->handleRequest('list');
        }
    }

    // Get single post by ID - now uses PostC controller
    public function getPostById($id) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view post.');
        }
        
        return $this->postController->detail($id);
    }
    
    // Get comments for a specific post - now uses CommentC controller
    public function getComments($postId) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view comments.');
        }
        
        return $this->commentController->getCommentsByPostId($postId);
    }

    // Get latest posts - now uses PostC controller
    public function getLatestPosts($limit = 3) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view posts.');
        }
        
        return $this->postController->recupererPosts($limit);
    }

    // Check if a user has reacted to a specific item
    public function hasUserReacted($itemId, $userId, $type = 'post') {
        if (!$this->checkPermission('view')) {
            return false;
        }
        
        if ($type === 'post') {
            return $this->postController->hasUserReacted($itemId, $userId);
        } else {
            return $this->commentController->hasUserReacted($itemId, $userId);
        }
    }

    // Helper for input sanitization (public so views can use it)
    public function sanitizeInput($data) {
        return htmlspecialchars(trim($data ?? ''));
    }
    
    // Add a new comment - wrapper for CommentC
    public function addComment($postId, $content) {
        if (!$this->checkPermission('addComment')) {
            throw new Exception('Permission denied to add comment.');
        }
        
        // Basic validation
        if (strlen($content) < 3) {
            throw new Exception('Comment must be at least 3 characters long.');
        }
        
        // Profanity check
        if ($this->containsProfanity($content)) {
            throw new Exception('Inappropriate content detected in your comment.');
        }
        
        return $this->commentController->ajouter($postId, $content, $_SESSION['user_id']);
    }
}