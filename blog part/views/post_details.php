<?php
require_once '../controllers/controller.php';

$controller = new BlogController();
$viewData = $controller->handleRequest(); // Get roles/login status etc.

$postId = $_GET['id'] ?? null;

if (!$postId || !filter_var($postId, FILTER_VALIDATE_INT)) { // Basic validation
    $_SESSION['error'] = 'Invalid Post ID.';
    header('Location: blog_frontend.php');
    exit;
}

$post = $controller->getPostById($postId);
if (!$post) {
    $_SESSION['error'] = 'Post not found.';
    header('Location: blog_frontend.php');
    exit;
}

$comments = $controller->getComments($postId);
$is_admin = $viewData['is_admin'] ?? false;
$is_logged_in = $viewData['is_logged_in'] ?? false;
$user_role = $viewData['user_role'] ?? 'visitor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="../../main_front/style.css"> <!-- Use front-office style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add some specific styles for post details */
        .post-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            color: #0a1d37;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .post-content {
            margin-bottom: 2rem;
            line-height: 1.7;
        }
        .post-meta {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 1rem;
        }
        .comments {
            margin-top: 3rem;
            border-top: 1px solid #eee;
            padding-top: 2rem;
        }
        .comments h2 {
            margin-bottom: 1.5rem;
        }
        .comments ul {
            list-style: none;
            padding: 0;
        }
        .comments li {
            background: #f8f9fa;
            border: 1px solid #eee;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .comment-content {
             flex-grow: 1;
             margin-right: 1rem;
        }
        .comment-meta {
            font-size: 0.8em;
            color: #777;
            margin-top: 0.5rem;
        }
        .comment-actions a {
            margin-left: 0.5rem;
            color: #007bff;
            text-decoration: none;
            font-size: 0.9em;
        }
        .comment-actions a.delete {
            color: #dc3545;
        }
        .comment-form {
            margin-top: 2rem;
        }
        .comment-form textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .comment-form input[type="submit"] {
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
        }
        .reaction-btn {
            color: #007bff;
            text-decoration: none;
            margin-left: 10px;
        }
        .reaction-btn i {
            margin-right: 3px;
        }
        .login-prompt {
            margin-top: 1rem;
            color: #555;
        }
        .user-status {
            background: rgba(255,255,255,0.9);
            color: #0a1d37;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .user-status.admin {
            background-color: #4c6ef5;
            color: white;
        }
        
        .user-status.user {
            background-color: #10b981;
            color: white;
        }
        
        .user-status.visitor {
            background-color: #f59e0b;
            color: white;
        }
        .message {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .message.error {
            background-color: #ffcccc;
            border: 1px solid #ff8888;
            color: #cc0000;
        }
        .message.success {
            background-color: #ccffcc;
            border: 1px solid #88ff88;
            color: #006600;
        }
    </style>
    <script>
        function validateCommentForm() {
            const comment = document.getElementById('comment').value.trim();
            
            if (!comment) {
                // Show error inline instead of alert
                const errorDiv = document.createElement('div');
                errorDiv.className = 'message error';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Comment cannot be empty.';
                
                const formElement = document.querySelector('.comment-form');
                formElement.prepend(errorDiv);
                
                // Auto-remove after 3 seconds
                setTimeout(() => errorDiv.remove(), 3000);
                
                document.getElementById('comment').focus();
                return false;
            }
            
            // Check length
            if (comment.length < 3) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'message error';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Comment must be at least 3 characters long.';
                
                const formElement = document.querySelector('.comment-form');
                formElement.prepend(errorDiv);
                
                setTimeout(() => errorDiv.remove(), 3000);
                
                document.getElementById('comment').focus();
                return false;
            }
            
            return true;
        }

        function confirmCommentDelete(commentId, postId) {
             if (confirm('Are you sure you want to delete this comment?')) {
                window.location.href = `../controllers/controller.php?action=deleteComment&id=${commentId}&post_id=${postId}`; // Point to controller
            }
            return false;
        }
    </script>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>

    <header>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <img src="../../main_front/logo.png" alt="WorldVenture Logo" class="logo">
            <span class="user-status <?= $user_role ?>">
                <i class="fas fa-user"></i> <?= ucfirst($user_role) ?> mode
            </span>
        </div>
        <div>
            <a href="blog_frontend.php" class="login-btn">Back to Blog</a>
            <?php if ($is_admin): ?>
                <a href="blog_backend.php" class="login-btn">Admin Panel</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="post-container">
        <!-- Display messages if any -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">
            <span>Published on: <?= date('F j, Y', strtotime($post['created_at'])) ?></span>
            <span> | Reactions: <?= $post['reactions'] ?? 0 ?></span>
            <?php if ($is_logged_in): ?>
                <a href="../controllers/controller.php?action=reactPost&id=<?= $postId ?>&origin=details" class="reaction-btn"><i class="far fa-thumbs-up"></i>React</a>
            <?php endif; ?>
        </div>
        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); // Use nl2br and ensure content is escaped if not HTML ?>
        </div>

        <section class="comments">
            <h2>Comments (<?= count($comments) ?>)</h2>
            <?php if (!$is_logged_in): ?>
                <div style="background: rgba(0,0,0,0.05); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <p><i class="fas fa-info-circle"></i> You are in visitor mode. <a href="#" onclick="alert('Login feature coming soon!')">Log in</a> to add comments and reactions.</p>
                </div>
            <?php endif; ?>
            <?php if (count($comments) > 0): ?>
                <ul>
                    <?php foreach ($comments as $comment): ?>
                    <li>
                        <div class="comment-content">
                            <?php echo htmlspecialchars($comment['content']); ?>
                            <div class="comment-meta">
                                Commented on: <?= date('F j, Y, g:i a', strtotime($comment['created_at'])) ?>
                                | Reactions: <?= $comment['reactions'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="comment-actions">
                             <?php if ($is_logged_in): ?>
                                <a href="../controllers/controller.php?action=reactComment&id=<?= $comment['id'] ?>&post_id=<?= $postId ?>" class="reaction-btn"><i class="far fa-thumbs-up"></i>React</a>
                             <?php endif; ?>
                             <?php if ($is_admin): // Show delete button only to admins ?>
                                <a href="#" onclick="return confirmCommentDelete(<?= $comment['id'] ?>, <?= $postId ?>)" class="delete">Delete</a>
                             <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>

            <?php if ($is_logged_in): // Show comment form only if logged in ?>
                <div class="comment-form">
                    <h3>Add a Comment</h3>
                    <form method="POST" action="../controllers/controller.php" onsubmit="return validateCommentForm();"> <!-- Point action to controller -->
                        <input type="hidden" name="action" value="addComment">
                        <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                        <textarea id="comment" name="comment" rows="4" placeholder="Write your comment here..."></textarea><br>
                        <input type="submit" value="Submit Comment">
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>