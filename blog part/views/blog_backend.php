<?php
require_once '../controllers/controller.php';
$controller = new BlogController();
$data = $controller->handleRequest();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Blog Management</title>
    <link rel="stylesheet" href="../../main_backoffice/css/style.css">
    <script>
        function validateForm() {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!title) {
                alert('Title is required.');
                document.getElementById('title').focus();
                return false;
            }
            
            if (!content) {
                alert('Content is required.');
                document.getElementById('content').focus();
                return false;
            }
            
            return true;
        }
        
        function confirmDelete(postId) {
            if (confirm('Are you sure you want to delete this post?')) {
                window.location.href = `?action=delete&id=${postId}`;
            }
            return false;
        }
    </script>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../../main_backoffice/images/logo-worldventure.png" alt="WorldVenture" class="admin-logo">
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../../main_backoffice/index.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="active"><a href="#"><i class="fas fa-blog"></i> Blog</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="admin-header">
                <h1><i class="fas fa-blog"></i> Blog Management</h1>
                <div>
                    <a href="blog_frontend.php" class="btn primary">View Blog</a>
                </div>
            </div>

            <div class="data-card">
                <h2>Add New Post</h2>
                <form method="POST" action="blog_backend.php" onsubmit="return validateForm();">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title">
                    </div>
                    <div class="form-group">
                        <label for="content">Content:</label>
                        <textarea id="content" name="content" rows="6"></textarea>
                    </div>
                    <button type="submit" class="btn primary">Add Post</button>
                </form>
            </div>

            <div class="data-card">
                <h2>All Posts</h2>
                <?php if (isset($data['posts']) && count($data['posts']) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Comments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['posts'] as $post): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($post['title']) ?></td>
                                        <td><?= $post['created_at'] ?></td>
                                        <td>
                                            <?php
                                            $commentCount = 0;
                                            foreach ($data['comments'] as $comment) {
                                                if ($comment['post_id'] == $post['id']) $commentCount++;
                                            }
                                            echo $commentCount;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?= $post['id'] ?>)" class="btn-sm">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No blog posts available.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>