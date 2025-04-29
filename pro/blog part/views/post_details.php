<?php
require_once '../controllers/controller.php';
require_once '../config/auth.php';

// Get post ID from URL parameter
$postId = $_GET['id'] ?? null;
if (!$postId) {
    header('Location: blog_frontend.php');
    exit;
}

// Initialize controller
$controller = new BlogController();

// Get post data
$post = $controller->getPostById($postId);
if (!$post) {
    header('Location: blog_frontend.php');
    exit;
}

// Get comments
$comments = $controller->getComments($postId);

// Handle comment submission
$commentError = '';
$commentSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isLoggedIn()) {
        $commentError = 'You must be logged in to add a comment.';
    } else {
        $comment = trim($_POST['comment']);
        if (empty($comment)) {
            $commentError = 'Comment cannot be empty.';
        } elseif (strlen($comment) < 3) {
            $commentError = 'Comment must be at least 3 characters long.';
        } else {
            try {
                $controller->addComment($postId, $comment);
                $commentSuccess = true;
                // Reload comments after adding a new one
                $comments = $controller->getComments($postId);
                // Clear the form data
                $_POST['comment'] = '';
            } catch (Exception $e) {
                $commentError = $e->getMessage();
            }
        }
    }
}

// Check if success message should be shown
$showSuccessMessage = isset($_GET['comment_success']) && $_GET['comment_success'] === 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - WorldVenture</title>
    <link rel="stylesheet" href="../../main_front/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .post-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .post-header {
            background: rgba(255,255,255,0.95);
            padding: 2rem;
            border-radius: 12px 12px 0 0;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-bottom: none;
        }
        
        .post-title {
            font-size: 2.2rem;
            color: #0b2447;
            margin-bottom: 1rem;
        }
        
        .post-meta {
            display: flex;
            justify-content: space-between;
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
        }
        
        .post-body {
            background: rgba(255,255,255,0.95);
            padding: 2rem;
            border-radius: 0 0 12px 12px;
            margin-bottom: 2rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-top: none;
            line-height: 1.8;
            color: #334155;
        }
        
        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }
        
        .post-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .reaction-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border: none;
            background: #f1f5f9;
            border-radius: 20px;
            cursor: pointer;
            color: #64748b;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .reaction-btn:hover, .reaction-btn.active {
            background: #e2e8f0;
            color: #0b2447;
            transform: translateY(-2px);
        }
        
        .reaction-btn.active {
            background: #dbeafe;
            color: #3b82f6;
        }
        
        .reaction-btn i {
            font-size: 1.2rem;
        }
        
        .comments-section {
            background: rgba(255,255,255,0.9);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .comments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .comments-header h2 {
            color: #0b2447;
            font-size: 1.5rem;
            margin: 0;
        }
        
        .comment-count {
            background: #e2e8f0;
            color: #64748b;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .comment-form {
            margin-bottom: 2rem;
        }
        
        .comment-input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
            resize: vertical;
            min-height: 100px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .comment-input:focus {
            border-color: #3e92cc;
            box-shadow: 0 0 0 3px rgba(62, 146, 204, 0.2);
            outline: none;
        }
        
        .comment-form-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .submit-comment {
            background: linear-gradient(135deg, #3e92cc, #0a4c8c);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .submit-comment:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(62, 146, 204, 0.4);
        }
        
        .visitor-message {
            text-align: center;
            padding: 1rem;
            background: #f1f5f9;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            color: #64748b;
        }
        
        .visitor-message a {
            color: #3e92cc;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .visitor-message a:hover {
            color: #0a4c8c;
            text-decoration: underline;
        }
        
        .comment-list {
            margin-top: 1.5rem;
        }
        
        .comment {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem 0;
            animation: fadeIn 0.5s ease-out;
        }
        
        .comment:last-child {
            border-bottom: none;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .comment-author {
            font-weight: 600;
            color: #0b2447;
        }
        
        .comment-role {
            display: inline-block;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            margin-left: 0.5rem;
            color: white;
        }
        
        .comment-role.admin {
            background: #0ea5e9;
        }
        
        .comment-role.user {
            background: #10b981;
        }
        
        .comment-date {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        
        .comment-content {
            line-height: 1.6;
            color: #334155;
            margin-bottom: 0.75rem;
        }
        
        .comment-actions {
            display: flex;
            gap: 1rem;
        }
        
        .comment-reaction {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            background: transparent;
            border: none;
            transition: all 0.2s;
        }
        
        .comment-reaction:hover, .comment-reaction.active {
            background: #f1f5f9;
            color: #0b2447;
        }
        
        .comment-reaction.active {
            color: #3b82f6;
        }
        
        .new-comment {
            animation: highlightNew 2s ease;
        }
        
        .toast {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #10b981;
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            transform: translateY(150%);
            transition: transform 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }
        
        .toast.show {
            transform: translateY(0);
        }
        
        .toast.error {
            background: #ef4444;
        }
        
        .toast i {
            font-size: 1.2rem;
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
            animation: fadeIn 0.3s ease-in;
        }
        
        .back-to-blog {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.4rem;
            background: rgba(255,255,255,0.85);
            border-radius: 20px;
            color: #334155;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            margin-bottom: 1rem;
        }
        
        .back-to-blog:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .success-message {
            background: #dcfce7;
            color: #10b981;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes highlightNew {
            0% { background-color: rgba(224, 242, 254, 0.6); }
            100% { background-color: transparent; }
        }
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>
    
    <header>
        <img src="../../main_front/logo.png" alt="WorldVenture Logo" class="logo">
        <div>
            <?php if (isLoggedIn()): ?>
                <div class="user-menu">
                    <div class="user-avatar" onclick="toggleUserMenu()">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
                        <span class="user-badge <?= getUserRole() ?>"><?= ucfirst(getUserRole()) ?></span>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <div class="dropdown-header">
                            Account Options
                        </div>
                        <?php if(getUserRole() === 'admin'): ?>
                            <a href="blog_backend.php">
                                <i class="fas fa-cog"></i> Admin Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="blog_frontend.php">
                            <i class="fas fa-home"></i> Blog Home
                        </a>
                        <hr>
                        <a href="../config/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Log Out
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn" style="margin-right: 10px;"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="../../main_front/index.php" class="login-btn">Return to Home</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="post-container">
        <a href="blog_frontend.php" class="back-to-blog">
            <i class="fas fa-arrow-left"></i> Back to Blog
        </a>
        
        <div class="post-header">
            <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <div>
                    <i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name'] ?? 'Author') ?>
                    <?php if ($post['author_id'] == 1): ?>
                        <span class="comment-role admin">Admin</span>
                    <?php endif; ?>
                </div>
                <div>
                    <i class="fas fa-calendar-alt"></i> <?= date('F j, Y', strtotime($post['created_at'])) ?>
                </div>
            </div>
        </div>
        
        <div class="post-body">
            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
            
            <div class="post-actions">
                <button 
                    id="reactionBtn" 
                    class="reaction-btn <?= isLoggedIn() && $controller->model->hasUserReacted($post['id'], $_SESSION['user_id']) ? 'active' : '' ?>"
                    <?= !isLoggedIn() ? 'disabled' : '' ?>
                    onclick="handleReaction(<?= $post['id'] ?>)"
                >
                    <i class="fas fa-thumbs-up"></i>
                    <span id="reactionCount"><?= $post['reactions'] ?></span> 
                    <?= $post['reactions'] == 1 ? 'Like' : 'Likes' ?>
                </button>
                
                <div>
                    <button class="reaction-btn" onclick="scrollToComments()">
                        <i class="fas fa-comments"></i>
                        <?= count($comments) ?> 
                        <?= count($comments) == 1 ? 'Comment' : 'Comments' ?>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="comments-section" id="comments">
            <div class="comments-header">
                <h2><i class="fas fa-comments"></i> Comments</h2>
                <span class="comment-count"><?= count($comments) ?> <?= count($comments) == 1 ? 'Comment' : 'Comments' ?></span>
            </div>
            
            <?php if ($commentSuccess || $showSuccessMessage): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Your comment has been added successfully!
            </div>
            <?php endif; ?>
            
            <?php if (getUserRole() === 'visitor'): ?>
            <div class="visitor-message">
                <i class="fas fa-info-circle"></i> You need to <a href="login.php">log in</a> to leave a comment.
            </div>
            <?php else: ?>
            <form class="comment-form" method="POST" action="">
                <textarea 
                    class="comment-input" 
                    name="comment" 
                    placeholder="Write your comment here..."
                    required
                    minlength="3"
                ><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
                
                <?php if ($commentError): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $commentError ?>
                </div>
                <?php endif; ?>
                
                <div class="comment-form-actions">
                    <button type="submit" class="submit-comment">
                        <i class="fas fa-paper-plane"></i> Post Comment
                    </button>
                </div>
            </form>
            <?php endif; ?>
            
            <div class="comment-list">
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment <?= $commentSuccess && $comment['id'] == $this->pdo->lastInsertId() ? 'new-comment' : '' ?>">
                        <div class="comment-header">
                            <div class="comment-author">
                                <?= htmlspecialchars($comment['author_name'] ?? 'Anonymous') ?>
                                <?php if ($comment['user_id'] == 1): ?>
                                    <span class="comment-role admin">Admin</span>
                                <?php elseif ($comment['user_id'] != 0): ?>
                                    <span class="comment-role user">User</span>
                                <?php endif; ?>
                            </div>
                            <div class="comment-date">
                                <?= date('M j, Y g:i A', strtotime($comment['created_at'])) ?>
                            </div>
                        </div>
                        <div class="comment-content">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                        <div class="comment-actions">
                            <button 
                                class="comment-reaction <?= isLoggedIn() && $controller->model->hasUserReacted($comment['id'], $_SESSION['user_id'], 'comment') ? 'active' : '' ?>"
                                <?= !isLoggedIn() ? 'disabled' : '' ?>
                                onclick="handleCommentReaction(<?= $comment['id'] ?>)"
                            >
                                <i class="fas fa-thumbs-up"></i>
                                <span id="comment-reaction-<?= $comment['id'] ?>"><?= $comment['reactions'] ?></span>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem 0; color: #64748b;">
                        <i class="fas fa-comment-slash" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>No comments yet. Be the first to comment!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        // Toggle user dropdown menu
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const userMenu = document.querySelector('.user-menu');
                const clickedOutside = !userMenu.contains(event.target);
                
                if (clickedOutside && dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                }
            });
        }
        
        // Handle post reaction
        function handleReaction(postId) {
            <?php if (getUserRole() === 'visitor'): ?>
            showToast('<i class="fas fa-lock"></i> Please login to react to posts', true);
            return;
            <?php endif; ?>
            
            const reactionBtn = document.getElementById('reactionBtn');
            const countElement = document.getElementById('reactionCount');
            
            // Optimistic UI update
            reactionBtn.classList.toggle('active');
            
            fetch('blog_backend.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ajax: true,
                    action: 'react',
                    postId: postId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    showToast(`<i class="fas fa-exclamation-circle"></i> ${data.error}`, true);
                    // Revert UI on error
                    reactionBtn.classList.toggle('active');
                    return;
                }
                
                if (data.success) {
                    countElement.textContent = data.count;
                    showToast('<i class="fas fa-check-circle"></i> Your reaction has been recorded!');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('<i class="fas fa-times-circle"></i> Error updating reaction', true);
                // Revert UI on error
                reactionBtn.classList.toggle('active');
            });
        }
        
        // Handle comment reaction
        function handleCommentReaction(commentId) {
            <?php if (getUserRole() === 'visitor'): ?>
            showToast('<i class="fas fa-lock"></i> Please login to react to comments', true);
            return;
            <?php endif; ?>
            
            const reactionBtn = event.currentTarget;
            const countElement = document.getElementById(`comment-reaction-${commentId}`);
            
            // Optimistic UI update
            reactionBtn.classList.toggle('active');
            
            fetch('blog_backend.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ajax: true,
                    action: 'reactToComment',
                    commentId: commentId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    showToast(`<i class="fas fa-exclamation-circle"></i> ${data.error}`, true);
                    // Revert UI on error
                    reactionBtn.classList.toggle('active');
                    return;
                }
                
                if (data.success) {
                    countElement.textContent = data.count;
                    showToast('<i class="fas fa-check-circle"></i> Your reaction has been recorded!');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('<i class="fas fa-times-circle"></i> Error updating reaction', true);
                // Revert UI on error
                reactionBtn.classList.toggle('active');
            });
        }
        
        // Scroll to comments section
        function scrollToComments() {
            document.getElementById('comments').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }
        
        // Enhanced toast notification
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.innerHTML = message;
            toast.className = isError ? 'toast error show' : 'toast show';
            
            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll to comments if there's a success message
            <?php if ($commentSuccess || $showSuccessMessage): ?>
            scrollToComments();
            <?php endif; ?>
        });
    </script>
</body>
</html>