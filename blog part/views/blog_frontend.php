<?php
require_once '../controllers/controller.php';
$controller = new BlogController();
$data = $controller->handleRequest();
$is_logged_in = $data['is_logged_in'] ?? false;
$user_role = $data['user_role'] ?? 'visitor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture Blog</title>
    <link rel="stylesheet" href="../../main_front/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .blog-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1rem;
        }
        
        .blog-post {
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            color: #0a1d37;
        }
        
        .blog-post h2 {
            color: #0b2447;
            margin-bottom: 1rem;
        }
        
        .blog-post p {
            margin-bottom: 1rem;
        }
        
        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            color: #555;
        }
        
        .reactions {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .reactions i {
            color: #0099cc;
        }
        
        .read-more {
            display: inline-block;
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .react-btn {
            background: none;
            border: none;
            color: #0099cc;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            transition: background 0.2s;
        }
        
        .react-btn:hover {
            background: rgba(0,153,204,0.1);
        }
        
        .login-notice {
            background: rgba(255,255,255,0.9);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #0a1d37;
        }

        .message {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background: rgba(255,255,255,0.9);
        }
        .message.error {
            background-color: rgba(255, 0, 0, 0.2);
            border: 1px solid #ff8888;
            color: #cc0000;
        }
        .message.success {
            background-color: rgba(0, 255, 0, 0.2);
            border: 1px solid #88ff88;
            color: #006600;
        }
    </style>
    <script>
        // Improved utility functions for validation and errors
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'message error';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
            
            const container = document.getElementById('message-container') || document.querySelector('.blog-container');
            container.prepend(errorDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => errorDiv.remove(), 5000);
        }
        
        function showSuccess(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'message success';
            successDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
            
            const container = document.getElementById('message-container') || document.querySelector('.blog-container');
            container.prepend(successDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => successDiv.remove(), 5000);
        }
        
        // Document ready handler
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Blog frontend loaded successfully');
            console.log('Current user role: <?= $user_role ?>');
            
            // Add any event listeners here
            const reactButtons = document.querySelectorAll('.react-btn');
            reactButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    <?php if (!$is_logged_in): ?>
                        e.preventDefault();
                        showError('You must be logged in to react to posts');
                    <?php endif; ?>
                });
            });
        });
    </script>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>
    
    <header>
        <img src="../../main_front/logo.png" alt="WorldVenture Logo" class="logo">
        <div>
            <a href="../../main_front/index.html" class="login-btn">Return to Home</a>
            <?php if (!$is_logged_in): ?>
                <a href="#" class="login-btn" onclick="alert('Login feature coming soon!')">Login</a>
            <?php else: ?>
                <a href="#" class="login-btn" onclick="alert('You are logged in as: <?= htmlspecialchars($data['user_role']) ?>')">
                    <?= htmlspecialchars(ucfirst($data['user_role'])) ?> Account
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div class="blog-container">
        <div class="blog-header">
            <h1>WorldVenture Blog</h1>
            <p>Discover travel stories, tips, and adventures</p>
        </div>

        <!-- Improved messages display -->
        <div id="message-container">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['message']) ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
        </div>

        <!-- User role indicator -->
        <div class="login-notice">
            <p>
                <i class="fas fa-user-circle"></i> 
                You are browsing as: <strong><?= ucfirst($user_role) ?></strong>
                <?php if (!$is_logged_in): ?>
                    - <a href="#" onclick="alert('Login feature coming soon!')">Log in</a> to react to posts and add comments.
                <?php endif; ?>
            </p>
        </div>

        <?php if (isset($data['posts']) && count($data['posts']) > 0): ?>
            <?php foreach ($data['posts'] as $post): ?>
                <article class="blog-post">
                    <div class="post-meta">
                        <span>Posted on <?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                        <div class="reactions">
                            <i class="fas fa-thumbs-up"></i>
                            <span><?= $post['reactions'] ?></span>
                            <?php if ($is_logged_in): ?>
                                <a href="../controllers/controller.php?action=reactPost&id=<?= $post['id'] ?>&origin=list" class="react-btn">
                                    <i class="far fa-thumbs-up"></i> React
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h2><?= htmlspecialchars($post['title']) ?></h2>
                    <p><?= htmlspecialchars(substr(strip_tags($post['content']), 0, 200)) ?>...</p>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <a href="post_details.php?id=<?= $post['id'] ?>" class="read-more">Read More</a>
                        <span><?= $post['comment_count'] ?> comments</span>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="blog-post">
                <h2>No Blog Posts Yet</h2>
                <p>Check back soon for exciting travel content!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>