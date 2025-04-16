<?php
require_once '../controllers/controller.php';

$controller = new BlogController();
$postId = $_GET['id'] ?? null;

if (!$postId) {
    header('Location: blog_frontend.php');
    exit;
}

$post = $controller->getPostById($postId);
if (!$post) {
    echo "<h1>Post not found</h1>";
    exit;
}

$comments = $controller->getComments($postId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="../../main_front/style.css">
    <script>
        function validateCommentForm() {
            const comment = document.getElementById('comment').value.trim();
            
            if (!comment) {
                alert('Comment content is required.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <a href="blog_frontend.php">Back to Blog</a>
    </header>
    <main>
        <div class="post-content">
            <?php echo $post['content']; ?>
        </div>
        <section class="comments">
            <h2>Comments</h2>
            <?php if (count($comments) > 0): ?>
                <ul>
                    <?php foreach ($comments as $comment): ?>
                    <li><?php echo htmlspecialchars($comment['content']); ?> 
                        <small>- <?php echo $comment['created_at']; ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>
            
            <form method="POST" action="blog_backend.php" onsubmit="return validateCommentForm();">
                <label for="comment">Add a Comment:</label><br>
                <textarea id="comment" name="comment" rows="4" cols="50" placeholder="Write your comment here..."></textarea><br><br>
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                <input type="hidden" name="action" value="addComment">
                <input type="submit" value="Submit Comment">
            </form>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 WorldVenture</p>
    </footer>
</body>
</html>