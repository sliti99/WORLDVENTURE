<?php
require_once '../controllers/controller.php';
$controller = new BlogController();
$data = $controller->handleRequest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture Blog</title>
    <link rel="stylesheet" href="../../main_front/style.css">
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
        
        .read-more {
            display: inline-block;
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .blog-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .blog-header h1 {
            color: white;
            font-size: 3rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>
    
    <header>
        <img src="../../main_front/logo.png" alt="WorldVenture Logo" class="logo">
        <a href="../../main_front/index.html" class="login-btn">Return to Home</a>
    </header>

    <div class="blog-container">
        <div class="blog-header">
            <h1>WorldVenture Blog</h1>
            <p>Discover travel stories, tips, and adventures</p>
        </div>

        <?php if (isset($data['posts']) && count($data['posts']) > 0): ?>
            <?php foreach ($data['posts'] as $post): ?>
                <article class="blog-post">
                    <h2><?= htmlspecialchars($post['title']) ?></h2>
                    <p><?= htmlspecialchars(substr(strip_tags($post['content']), 0, 200)) ?>...</p>
                    <a href="post_details.php?id=<?= $post['id'] ?>" class="read-more">Read More</a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="blog-post">
                <h2>No Blog Posts Yet</h2>
                <p>Check back soon for exciting travel content!</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add any interactive features here
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Blog frontend loaded successfully');
        });
    </script>
</body>
</html>