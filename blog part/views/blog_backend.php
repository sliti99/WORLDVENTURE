<?php
require_once '../controllers/controller.php';
$controller = new BlogController();
$data = $controller->handleRequest();
$is_admin = $data['is_admin'] ?? false; // Get admin status from controller
?>
<!DOCTYPE html>
<html>
<head>
    <title>Blog Management</title>
    <link rel="stylesheet" href="../../main_backoffice/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> <!-- Ensure Font Awesome is linked -->
    <style>
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
        function validateForm() {
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
        
        function confirmDelete(postId) {
            if (confirm('Are you sure you want to delete this post and all its comments?')) {
                window.location.href = `../controllers/controller.php?action=delete&id=${postId}`;
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
                    <li class="active"><a href="blog_backend.php"><i class="fas fa-blog"></i> Blog Management</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="admin-header">
                <h1><i class="fas fa-blog"></i> Blog Management</h1>
                <div>
                    <a href="blog_frontend.php" class="btn primary" target="_blank">View Blog</a>
                </div>
            </div>

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

            <?php if ($is_admin): // Only show forms/tables if admin ?>
                <div class="data-card">
                    <h2>Add New Post</h2>
                    <form method="POST" action="../controllers/controller.php" onsubmit="return validateForm();"> <!-- Point form action to the controller -->
                        <input type="hidden" name="action" value="create">
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" id="title" name="title" class="form-control"> <!-- Added form-control class if available in your CSS -->
                        </div>
                        <div class="form-group">
                            <label for="content">Content:</label>
                            <textarea id="content" name="content" rows="6" class="form-control"></textarea> <!-- Added form-control class -->
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
                                        <th>Reactions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['posts'] as $post): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($post['title']) ?></td>
                                            <td><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                                            <td><?= $post['comment_count'] ?? 0 ?></td>
                                            <td><?= $post['reactions'] ?? 0 ?></td>
                                            <td>
                                                <a href="edit_post.php?action=edit&id=<?= $post['id'] ?>" class="btn-sm btn-outline">Edit</a> <!-- Link to edit page -->
                                                <a href="#" onclick="return confirmDelete(<?= $post['id'] ?>)" class="btn-sm btn-danger">Delete</a> <!-- Use btn-danger or similar for delete -->
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
            <?php else: ?>
                <p>You do not have permission to manage blog posts.</p>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>