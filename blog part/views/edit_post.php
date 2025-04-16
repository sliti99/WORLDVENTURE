<?php
require_once '../controllers/controller.php';
$controller = new BlogController();

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Access Denied: You must be an admin to edit posts.');
}

$postId = $_GET['id'] ?? null;
$postData = null;

if ($postId) {
    $postData = $controller->getPostById($postId);
}

if (!$postData) {
    die('Post not found or invalid ID.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <link rel="stylesheet" href="../../main_backoffice/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        function validateEditForm() {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            let isValid = true;
            let errorMessage = '';
            
            // Clear previous error if any
            const existingError = document.querySelector('.form-error');
            if (existingError) {
                existingError.remove();
            }
            
            if (!title) {
                errorMessage = 'Title is required.';
                isValid = false;
                document.getElementById('title').focus();
            } else if (title.length < 5) {
                errorMessage = 'Title must be at least 5 characters long.';
                isValid = false;
                document.getElementById('title').focus();
            }
            
            if (!content) {
                errorMessage = 'Content is required.';
                isValid = false;
                document.getElementById('content').focus();
            } else if (content.length < 10) {
                errorMessage = 'Content must be at least 10 characters long.';
                isValid = false;
                document.getElementById('content').focus();
            }
            
            if (!isValid) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'message error form-error';
                errorDiv.textContent = errorMessage;
                
                const form = document.querySelector('form');
                form.insertAdjacentElement('afterbegin', errorDiv);
                
                return false;
            }
            
            return true;
        }
    </script>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <!-- Re-use sidebar structure from blog_backend.php -->
             <div class="sidebar-header">
                <img src="../../main_backoffice/images/logo-worldventure.png" alt="WorldVenture" class="admin-logo">
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../../main_backoffice/index.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="blog_backend.php"><i class="fas fa-blog"></i> Blog Management</a></li>
                    <!-- Add other links -->
                </ul>
            </nav>
             <div class="sidebar-footer">
                <button class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </button>
            </div>
        </aside>

        <main class="main-content">
            <div class="admin-header">
                <h1><i class="fas fa-edit"></i> Edit Post</h1>
                <a href="blog_backend.php" class="btn btn-outline">Back to List</a>
            </div>

            <div class="data-card">
                <form method="POST" action="../controllers/controller.php" onsubmit="return validateEditForm();"> <!-- Point action to controller -->
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($postData['id']) ?>">

                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($postData['title']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="content">Content:</label>
                        <textarea id="content" name="content" rows="10" class="form-control"><?= htmlspecialchars($postData['content']) ?></textarea>
                    </div>

                    <button type="submit" class="btn primary">Update Post</button>
                </form>
            </div>
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
