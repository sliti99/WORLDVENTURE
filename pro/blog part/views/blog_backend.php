<?php
require_once '../controllers/controller.php';

// Force admin role in backend
$_SESSION['role'] = 'admin';
$_SESSION['user_id'] = 1;

$controller = new BlogController();
$data = $controller->handleRequest();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Blog Management - WorldVenture</title>
    <link rel="stylesheet" href="../../main_backoffice/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        
        .form-group textarea {
            min-height: 150px;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn.primary {
            background: var(--bleu);
            color: white;
        }
        
        .btn.primary:hover {
            background: var(--bleu-fonce);
        }
        
        .btn-sm {
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-sm:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        
        .btn-sm.danger {
            background: #fee2e2;
            color: #ef4444;
        }
        
        .btn-sm.danger:hover {
            background: #fecaca;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge.primary {
            background: #e0f2fe;
            color: #0ea5e9;
        }
        
        .badge.success {
            background: #dcfce7;
            color: #10b981;
        }
        
        .post-preview {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin: 0;
        }
        
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
            transform: translateY(150%);
            transition: transform 0.3s ease-out;
        }
        
        .toast.show {
            transform: translateY(0);
        }
        
        .toast.error {
            background: #ef4444;
        }
        
        .action-btn-group {
            display: flex;
            gap: 0.5rem;
        }
    </style>
    <script>
        function validateForm() {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            
            if (!title) {
                showToast('Title is required', true);
                document.getElementById('title').focus();
                return false;
            }
            
            if (!content) {
                showToast('Content is required', true);
                document.getElementById('content').focus();
                return false;
            }
            
            if (content.length < 20) {
                showToast('Content should be at least 20 characters', true);
                document.getElementById('content').focus();
                return false;
            }
            
            return true;
        }
        
        function confirmDelete(postId, postTitle) {
            if (confirm(`Are you sure you want to delete "${postTitle}"?`)) {
                window.location.href = `?action=delete&id=${postId}`;
            }
            return false;
        }
        
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = isError ? 'toast error show' : 'toast show';
            
            setTimeout(() => {
                toast.className = 'toast';
            }, 3000);
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
                    <li><a href="blog_frontend.php"><i class="fas fa-globe"></i> View Blog</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <button class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </aside>

        <main class="main-content">
            <div class="admin-header">
                <h1><i class="fas fa-blog"></i> Blog Management</h1>
                <div>
                    <a href="blog_frontend.php" class="btn primary">
                        <i class="fas fa-eye"></i> View Blog
                    </a>
                </div>
            </div>

            <div class="data-card">
                <h2><i class="fas fa-plus-circle"></i> Add New Post</h2>
                <form method="POST" action="blog_backend.php" onsubmit="return validateForm();">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" placeholder="Enter post title...">
                    </div>
                    <div class="form-group">
                        <label for="content">Content:</label>
                        <textarea id="content" name="content" rows="6" placeholder="Write your post content..."></textarea>
                    </div>
                    <button type="submit" class="btn primary">
                        <i class="fas fa-plus"></i> Add Post
                    </button>
                </form>
            </div>

            <div class="data-card">
                <h2><i class="fas fa-list"></i> All Posts</h2>
                <?php if (isset($data['posts']) && count($data['posts']) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Preview</th>
                                    <th>Date</th>
                                    <th>Stats</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['posts'] as $post): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($post['title']) ?></td>
                                        <td>
                                            <p class="post-preview"><?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...</p>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($post['created_at'])) ?></td>
                                        <td>
                                            <span class="badge primary">
                                                <i class="fas fa-thumbs-up"></i> <?= $post['reactions'] ?>
                                            </span>
                                            <span class="badge success">
                                                <i class="fas fa-comment"></i>
                                                <?php
                                                $commentCount = 0;
                                                foreach ($data['comments'] as $comment) {
                                                    if ($comment['post_id'] == $post['id']) $commentCount++;
                                                }
                                                echo $commentCount;
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-btn-group">
                                                <a href="post_details.php?id=<?= $post['id'] ?>" class="btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="javascript:void(0);" 
                                                   onclick="confirmDelete(<?= $post['id'] ?>, '<?= htmlspecialchars(addslashes($post['title'])) ?>')" 
                                                   class="btn-sm danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert">
                        <p><i class="fas fa-info-circle"></i> No blog posts available. Create your first post above!</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="toast" class="toast"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Any additional JavaScript for the admin panel
        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submission feedback
            <?php if (isset($_GET['success'])): ?>
                showToast('Post created successfully!');
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
                showToast('Post deleted successfully!');
            <?php endif; ?>
        });
    </script>
</body>
</html>