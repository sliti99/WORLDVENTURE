<?php
require_once '../models/model.php';

class BlogController {
    private $model;
    private $baseUrl;

    public function __construct() {
        // Initialize with try-catch for better error handling
        try {
            $this->model = new BlogModel();
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Get base URL for absolute paths in redirects
            $this->baseUrl = $this->getBaseUrl();
            
            // Set default user role if not already set
            if (!isset($_SESSION['user_role'])) {
                // Uncomment ONE of these blocks for testing:
                
                // Default visitor role (read-only)
                // $_SESSION['user_role'] = 'visitor'; 
                // $_SESSION['user_id'] = 0;
                
                // Regular user role (can comment and react)
                $_SESSION['user_role'] = 'user';    
                $_SESSION['user_id'] = 2;           
                
                // Admin role (full access)
                //$_SESSION['user_role'] = 'admin';   
                //$_SESSION['user_id'] = 1;           
            }
            
            // Ensure user_id is set if role is set
            if (isset($_SESSION['user_role']) && !isset($_SESSION['user_id'])) {
                if ($_SESSION['user_role'] === 'admin') {
                    $_SESSION['user_id'] = 1;
                } else if ($_SESSION['user_role'] === 'user') {
                    $_SESSION['user_id'] = 2;
                } else { // visitor
                    $_SESSION['user_id'] = 0;
                }
            }
        } catch (Exception $e) {
            $this->emergencyErrorDisplay('Controller initialization failed: ' . $e->getMessage());
        }
    }

    // Calculate base URL for absolute redirects
    private function getBaseUrl() {
        try {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            
            // Get the script name path and extract folder structure correctly
            $scriptPath = $_SERVER['SCRIPT_NAME'];
            
            // Handle the special case of our folder name with a space
            $folderPath = '/web.pro/inst/pro/blog part/';
            
            // Find position of this folder in the script path
            if (stripos($scriptPath, '/blog part/') !== false || 
                stripos($scriptPath, '/blog%20part/') !== false) {
                
                // URL encode the path properly - ensure the space is preserved as %20
                $baseDir = '/web.pro/inst/pro/blog%20part/';
            } else {
                // Fallback to standard path calculation
                $baseDir = dirname(dirname($_SERVER['SCRIPT_NAME']));
                $baseDir = str_replace('\\', '/', $baseDir); 
            }
            
            // Make sure we have a trailing slash
            if (substr($baseDir, -1) !== '/') {
                $baseDir .= '/';
            }
            
            // Log the calculated URL for debugging
            error_log("Base URL calculated as: " . $protocol . $host . $baseDir);
            
            return $protocol . $host . $baseDir;
        } catch (Exception $e) {
            $this->emergencyErrorDisplay('Failed to calculate base URL: ' . $e->getMessage());
            return '/'; // Fallback
        }
    }

    // Helper to check if the user has admin rights
    private function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    // Helper to check if user is logged in (admin or regular user)
    private function isLoggedIn() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'visitor';
    }

    public function handleRequest() {
        try {
            $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
            $id = $_GET['id'] ?? $_POST['id'] ?? null;

            $is_admin = $this->isAdmin();
            $is_logged_in = $this->isLoggedIn();
            
            // Process actions that modify data
            switch ($action) {
                case 'create':
                    if ($is_admin) $this->createPost();
                    else $this->showError('Access Denied: Only admins can create posts');
                    break;
                case 'update':
                    if ($is_admin) $this->updatePost();
                    else $this->showError('Access Denied: Only admins can update posts');
                    break;
                case 'delete':
                    if ($is_admin) $this->deletePost();
                    else $this->showError('Access Denied: Only admins can delete posts');
                    break;
                case 'addComment':
                    if ($is_logged_in) $this->addComment();
                    else $this->showError('Access Denied: Please log in to comment');
                    break;
                case 'deleteComment':
                    if ($is_admin) $this->deleteComment();
                    else $this->showError('Access Denied: Only admins can delete comments');
                    break;
                case 'reactPost':
                    if ($is_logged_in) $this->reactPost();
                    else $this->showError('Access Denied: Please log in to react to posts');
                    break;
                case 'reactComment':
                    if ($is_logged_in) $this->reactComment();
                    else $this->showError('Access Denied: Please log in to react to comments');
                    break;
                default:
                    // If this is a direct access to controller.php, redirect to the frontend
                    if (basename($_SERVER['SCRIPT_NAME']) === 'controller.php') {
                        header("Location: " . $this->baseUrl . "views/blog_frontend.php");
                        exit;
                    }
                    break;
            }

            // Return data for views
            return [
                'posts' => $this->model->getAllPosts(),
                'comments' => $this->model->getAllComments(),
                'is_admin' => $is_admin,
                'is_logged_in' => $is_logged_in,
                'user_role' => $_SESSION['user_role'] ?? 'visitor',
                'user_id' => $_SESSION['user_id'] ?? null
            ];
        } catch (Exception $e) {
            $this->showError('An error occurred while processing your request: ' . $e->getMessage());
            return []; // Return empty array as fallback
        }
    }

    // Emergency error display when we can't use the normal error handling
    private function emergencyErrorDisplay($message) {
        // Log the error
        error_log('BLOG CRITICAL ERROR: ' . $message);
        
        // Get the correct URL for the link back
        $backUrl = '/web.pro/inst/pro/blog%20part/views/blog_frontend.php';
        
        // Display an error page that will help diagnose the issue
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
                .error { background: #ffdddd; border: 1px solid #ff0000; padding: 15px; border-radius: 5px; }
                .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; }
                .back-link { display: inline-block; margin-top: 20px; color: #0066cc; text-decoration: none; }
                .back-link:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <h1>An error occurred</h1>
            <div class="error">' . htmlspecialchars($message) . '</div>
            <h2>Debug Information:</h2>
            <div class="code">
                <p>URL: ' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'unknown') . '</p>
                <p>Referring Page: ' . htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'unknown') . '</p>
            </div>
            <a href="' . $backUrl . '" class="back-link">← Return to the Blog</a>
        </body>
        </html>';
        exit;
    }

    public function getPostById($id) {
        return $this->model->getPostById($id);
    }

    public function getComments($postId) {
        return $this->model->getCommentsByPostId($postId);
    }

    private function createPost() {
        $title = $this->sanitizeInput($_POST['title'] ?? '');
        $content = $this->sanitizeInput($_POST['content'] ?? '');

        if (!$title || !$content) {
            // Consider redirecting back with an error message
            $this->showError('Title and content are required.');
        }

        $this->model->createPost($title, $content);
        // Redirect using absolute URL
        header('Location: ' . $this->baseUrl . 'views/blog_backend.php');
        exit;
    }

     private function updatePost() {
        $id = $this->sanitizeInput($_POST['id'] ?? null);
        $title = $this->sanitizeInput($_POST['title'] ?? '');
        $content = $this->sanitizeInput($_POST['content'] ?? '');

        if (!$id || !$title || !$content) {
            $this->showError('ID, Title and content are required for update.');
        }

        $this->model->updatePost($id, $title, $content);
         // Redirect using absolute URL
        header('Location: ' . $this->baseUrl . 'views/blog_backend.php');
        exit;
    }


    private function deletePost() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->showError('Post ID is required for deletion.');
        }

        $this->model->deletePost($id);
        // Redirect using absolute URL
        header('Location: ' . $this->baseUrl . 'views/blog_backend.php');
        exit;
    }

     private function deleteComment() {
        $id = $_GET['id'] ?? null;
        $postId = $_GET['post_id'] ?? null;

        if (!$id) {
            $this->showError('Comment ID is required for deletion.');
        }
        if (!$postId) {
             $this->showError('Post ID is required for redirection after comment deletion.');
        }


        $this->model->deleteComment($id);

        // Redirect using absolute URL
        header("Location: " . $this->baseUrl . "views/post_details.php?id=$postId");
        exit;
    }


    private function addComment() {
        $postId = $_POST['post_id'] ?? null;
        $content = $this->sanitizeInput($_POST['comment'] ?? '');

        if (!$postId || !$content) {
             $this->showError('Post ID and comment content are required.');
        }
        // Get actual user ID from session
        $userId = $_SESSION['user_id'] ?? 1;

        $this->model->addComment($postId, $content, $userId); // Pass user ID to model
        // Redirect using absolute URL
        header("Location: " . $this->baseUrl . "views/post_details.php?id=$postId");
        exit;
    }

    private function reactPost() {
        try {
            $id = $_GET['id'] ?? null;
            $origin = $_GET['origin'] ?? 'details'; // To redirect back correctly
            $userId = $_SESSION['user_id'] ?? 0;

            if (!$id) {
                $this->showError('Post ID is required to react.');
                return;
            }
            
            if ($userId === 0) {
                $this->showError('You must be logged in to react to posts.');
                return;
            }
            
            if ($this->model->hasUserReacted('post', $id, $userId)) {
                $_SESSION['message'] = "You've already reacted to this post";
            } else {
                $this->model->incrementPostReaction($id, $userId);
                $_SESSION['message'] = "Thanks for your reaction!";
            }

            // Create absolute URLs for redirection
            $redirectUrl = ($origin === 'list') 
                ? $this->baseUrl . "views/blog_frontend.php" 
                : $this->baseUrl . "views/post_details.php?id=$id";
            
            // Ensure the URL is properly formed
            $redirectUrl = filter_var($redirectUrl, FILTER_SANITIZE_URL);
            
            // Debug info
            error_log("Redirecting to: " . $redirectUrl);
            
            // Redirect and exit
            header("Location: $redirectUrl");
            exit;
        } catch (Exception $e) {
            $this->emergencyErrorDisplay("Failed to process post reaction: " . $e->getMessage());
        }
    }

    private function reactComment() {
        try {
            $id = $_GET['id'] ?? null;
            $postId = $_GET['post_id'] ?? null;
            $userId = $_SESSION['user_id'] ?? 0;

            if (!$id || !$postId) {
                $this->showError('Comment ID and Post ID are required to react.');
                return;
            }
            
            if ($userId === 0) {
                $this->showError('You must be logged in to react to comments.');
                return;
            }
            
            if ($this->model->hasUserReacted('comment', $id, $userId)) {
                $_SESSION['message'] = "You've already reacted to this comment";
            } else {
                $this->model->incrementCommentReaction($id, $userId);
                $_SESSION['message'] = "Thanks for your reaction!";
            }
            
            // Create absolute URL for redirection
            $redirectUrl = $this->baseUrl . "views/post_details.php?id=$postId";
            $redirectUrl = filter_var($redirectUrl, FILTER_SANITIZE_URL);
            
            // Debug info
            error_log("Redirecting to: " . $redirectUrl);
            
            // Redirect and exit
            header("Location: $redirectUrl");
            exit;
        } catch (Exception $e) {
            $this->emergencyErrorDisplay("Failed to process comment reaction: " . $e->getMessage());
        }
    }

    // Improved sanitization
    private function sanitizeInput($data) {
        return htmlspecialchars(trim(stripslashes($data)));
    }

    // Helper function for showing errors - updated with absolute paths
    private function showError($message) {
        try {
            // Store error in session instead of using die()
            $_SESSION['error'] = $message;
            
            // Debug information to help troubleshoot
            error_log("Blog Error: $message");
            
            // Determine where to redirect
            if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
                $redirectUrl = $_SERVER['HTTP_REFERER'];
            } else {
                $redirectUrl = $this->baseUrl . "views/blog_frontend.php";
            }
            
            // Ensure URL is valid
            $redirectUrl = filter_var($redirectUrl, FILTER_SANITIZE_URL);
            
            // Redirect and exit
            header("Location: $redirectUrl");
            exit;
        } catch (Exception $e) {
            $this->emergencyErrorDisplay("Error in error handling: " . $e->getMessage());
        }
    }
}

// Direct execution protection and error handler for controller.php
if (basename($_SERVER['SCRIPT_FILENAME']) === 'controller.php') {
    try {
        $controller = new BlogController();
        $result = $controller->handleRequest();
        
        // If we get here and haven't redirected, it's likely a direct access
        // Redirect to the frontend as a safety measure
        if (!headers_sent()) {
            header("Location: " . $controller->getBaseUrl() . "views/blog_frontend.php");
            exit;
        }
    } catch (Exception $e) {
        // Last resort error handling
        error_log("CRITICAL CONTROLLER ERROR: " . $e->getMessage());
        echo '<div style="color:red; padding:20px; border:1px solid red;">';
        echo '<h2>Error</h2>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><a href="/web.pro/inst/pro/blog%20part/views/blog_frontend.php">Return to Blog</a></p>';
        echo '</div>';
    }
}
?>