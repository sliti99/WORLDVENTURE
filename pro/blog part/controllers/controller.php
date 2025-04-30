<?php
require_once '../config/config.php';

class BlogController {
    private $model;
    private $pdo;

    public function __construct() {
        $this->model = new BlogModel();
        $this->pdo = Database::getInstance()->getConnection();
        
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
                    // SQL queries moved into controller
                    $stmt = $this->pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
                    $postsData = $stmt->fetchAll();
                    
                    $posts = [];
                    foreach ($postsData as $postData) {
                        $post = new Post();
                        $post->setId($postData['id']);
                        $post->setTitle($postData['title']);
                        $post->setContent($postData['content']);
                        $post->setAuthorId($postData['author_id']);
                        $post->setPhotoPath($postData['photo_path']);
                        $post->setLatitude($postData['latitude']);
                        $post->setLongitude($postData['longitude']);
                        $post->setCreatedAt($postData['created_at']);
                        
                        // Get reaction count for this post
                        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :postId AND type = 'post'");
                        $stmt->execute(['postId' => $post->getId()]);
                        $post->setReactions($stmt->fetchColumn());
                        
                        $posts[] = $post;
                    }
                    $this->model->setPosts($posts);
                    
                    // Get comments
                    $stmt = $this->pdo->query("SELECT c.*, u.name AS author_name FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.created_at ASC");
                    $commentsData = $stmt->fetchAll();
                    
                    $comments = [];
                    foreach ($commentsData as $commentData) {
                        $comment = new Comment();
                        $comment->setId($commentData['id']);
                        $comment->setPostId($commentData['post_id']);
                        $comment->setContent($commentData['content']);
                        $comment->setUserId($commentData['user_id']);
                        $comment->setCreatedAt($commentData['created_at']);
                        $comment->setAuthorName($commentData['author_name']);
                        
                        // Get reaction count for this comment
                        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :commentId AND type = 'comment'");
                        $stmt->execute(['commentId' => $comment->getId()]);
                        $comment->setReactions($stmt->fetchColumn());
                        
                        $comments[] = $comment;
                    }
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
                    $stmt = $this->pdo->query("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC");
                    $postsData = $stmt->fetchAll();
                    
                    $posts = [];
                    foreach ($postsData as $postData) {
                        $post = new Post();
                        $post->setId($postData['id']);
                        $post->setTitle($postData['title']);
                        $post->setContent($postData['content']);
                        $post->setAuthorId($postData['author_id']);
                        $post->setPhotoPath($postData['photo_path']);
                        $post->setLatitude($postData['latitude']);
                        $post->setLongitude($postData['longitude']);
                        $post->setCreatedAt($postData['created_at']);
                        
                        // Get reaction count
                        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :postId AND type = 'post'");
                        $stmt->execute(['postId' => $post->getId()]);
                        $post->setReactions($stmt->fetchColumn());
                        
                        $posts[] = $post;
                    }
                    $this->model->setPosts($posts);
                    
                    // Get comments
                    $stmt = $this->pdo->query("SELECT c.*, u.name AS author_name FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.created_at ASC");
                    $commentsData = $stmt->fetchAll();
                    
                    $comments = [];
                    foreach ($commentsData as $commentData) {
                        $comment = new Comment();
                        $comment->setId($commentData['id']);
                        $comment->setPostId($commentData['post_id']);
                        $comment->setContent($commentData['content']);
                        $comment->setUserId($commentData['user_id']);
                        $comment->setCreatedAt($commentData['created_at']);
                        $comment->setAuthorName($commentData['author_name']);
                        
                        // Get reaction count
                        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :commentId AND type = 'comment'");
                        $stmt->execute(['commentId' => $comment->getId()]);
                        $comment->setReactions($stmt->fetchColumn());
                        
                        $comments[] = $comment;
                    }
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

                // SQL query in controller
                $stmt = $this->pdo->prepare("INSERT INTO posts (title, content, author_id, created_at) VALUES (:title, :content, :authorId, NOW())");
                $stmt->execute([
                    'title' => $title,
                    'content' => $content,
                    'authorId' => $_SESSION['user_id']
                ]);
                $postId = $this->pdo->lastInsertId();
                
                if ($isAjax) {
                    $post = $this->getPostByIdRaw($postId);
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

                // SQL query in controller
                $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
                $stmt->execute(['id' => $id]);
                
                if ($isAjax) {
                    echo json_encode(['success' => true]);
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

                // SQL query in controller
                $stmt = $this->pdo->prepare("INSERT INTO comments (post_id, content, user_id, created_at) VALUES (:postId, :content, :userId, NOW())");
                $stmt->execute([
                    'postId' => $postId,
                    'content' => $content,
                    'userId' => $_SESSION['user_id']
                ]);
                $commentId = $this->pdo->lastInsertId();
                
                // AJAX comment submission
                if ($isAjax) {
                    $stmt = $this->pdo->prepare("SELECT c.*, u.name AS author_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = :id");
                    $stmt->execute(['id' => $commentId]);
                    $commentData = $stmt->fetch();
                    
                    echo json_encode(['success' => true, 'comment' => $commentData]);
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
                
                // SQL query in controller
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='post'");
                $stmt->execute(['userId' => $_SESSION['user_id'], 'itemId' => $postId]);
                
                if ((int)$stmt->fetchColumn() > 0) {
                    $stmt = $this->pdo->prepare("DELETE FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='post'");
                    $stmt->execute(['userId' => $_SESSION['user_id'], 'itemId' => $postId]);
                } else {
                    $stmt = $this->pdo->prepare("INSERT INTO reactions_log (user_id, item_id, type, created_at) VALUES (:userId, :itemId, 'post', NOW())");
                    $stmt->execute(['userId' => $_SESSION['user_id'], 'itemId' => $postId]);
                }
                
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id=:itemId AND type='post'");
                $stmt->execute(['itemId' => $postId]);
                $count = (int)$stmt->fetchColumn();
                
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='post'");
                $stmt->execute(['userId' => $_SESSION['user_id'], 'itemId' => $postId]);
                $hasReacted = (int)$stmt->fetchColumn() > 0;
                
                echo json_encode(['success' => true, 'count' => $count, 'hasReacted' => $hasReacted]);
                exit;

            case 'reactToComment':
                if (!$isAjax) die('Invalid request method for comment reaction.');
                
                $commentId = $requestData['commentId'] ?? null;
                if (!$commentId) {
                    echo json_encode(['error' => 'Comment ID is required for reaction']);
                    exit;
                }
                
                // SQL query in controller
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='comment'");
                $stmt->execute(['userId' => $_SESSION['user_id'], 'itemId' => $commentId]);
                
                if ((int)$stmt->fetchColumn() > 0) {
                    $stmt = $this->pdo->prepare("DELETE FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='comment'");
                    $stmt->execute(['userId' => $_SESSION['user_id'], 'itemId' => $commentId]);
                } else {
                    $stmt = $this->pdo->prepare("INSERT INTO reactions_log (user_id, item_id, type, created_at) VALUES (:userId, :itemId, 'comment', NOW())");
                    $stmt->execute(['userId' => $_SESSION['user_id'], 'itemId' => $commentId]);
                }
                
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id=:itemId AND type='comment'");
                $stmt->execute(['itemId' => $commentId]);
                $count = (int)$stmt->fetchColumn();
                
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
                
                // SQL query in controller
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type=:type");
                $stmt->execute([
                    'userId' => $_SESSION['user_id'],
                    'itemId' => $itemId,
                    'type' => $type
                ]);
                $hasReacted = (int)$stmt->fetchColumn() > 0;
                
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

    // Get single post by ID - returns as an array for use in views
    public function getPostById($id) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view post.');
        }
        
        // SQL query in controller
        $stmt = $this->pdo->prepare("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $postData = $stmt->fetch();
        
        if (!$postData) {
            return null;
        }
        
        // Get reaction count
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :postId AND type = 'post'");
        $stmt->execute(['postId' => $id]);
        $postData['reactions'] = (int)$stmt->fetchColumn();
        
        return $postData;
    }
    
    // Helper method that returns raw post data (used internally)
    private function getPostByIdRaw($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $postData = $stmt->fetch();
        
        if (!$postData) {
            return null;
        }
        
        // Get reaction count
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :postId AND type = 'post'");
        $stmt->execute(['postId' => $id]);
        $postData['reactions'] = (int)$stmt->fetchColumn();
        
        return $postData;
    }
    
    // Get comments for a specific post - returns as array for views
    public function getComments($postId) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view comments.');
        }
        
        // SQL query in controller
        $stmt = $this->pdo->prepare("SELECT c.*, u.name AS author_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = :postId ORDER BY c.created_at ASC");
        $stmt->execute(['postId' => $postId]);
        $commentsData = $stmt->fetchAll();
        
        // Get reaction counts
        foreach ($commentsData as &$comment) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :commentId AND type = 'comment'");
            $stmt->execute(['commentId' => $comment['id']]);
            $comment['reactions'] = (int)$stmt->fetchColumn();
        }
        
        return $commentsData;
    }

    // Get latest posts - returns as array for views
    public function getLatestPosts($limit = 3) {
        if (!$this->checkPermission('view')) {
            die('Permission denied to view posts.');
        }
        
        // SQL query in controller
        $stmt = $this->pdo->prepare("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC LIMIT :limit");
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $postsData = $stmt->fetchAll();
        
        // Get reaction counts
        foreach ($postsData as &$post) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :postId AND type = 'post'");
            $stmt->execute(['postId' => $post['id']]);
            $post['reactions'] = (int)$stmt->fetchColumn();
        }
        
        return $postsData;
    }

    // Check if a user has reacted to a specific item
    public function hasUserReacted($itemId, $userId, $type = 'post') {
        if (!$this->checkPermission('view')) {
            return false;
        }
        
        if (!$userId || $userId === 0) {
            return false;
        }
        
        // SQL query in controller
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id = :userId AND item_id = :itemId AND type = :type");
        $stmt->execute([
            'userId' => $userId,
            'itemId' => $itemId,
            'type' => $type
        ]);
        
        return (int)$stmt->fetchColumn() > 0;
    }

    // Helper for input sanitization (public so views can use it)
    public function sanitizeInput($data) {
        return htmlspecialchars(trim($data ?? ''));
    }
}