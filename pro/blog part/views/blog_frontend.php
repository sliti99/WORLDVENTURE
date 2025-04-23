<?php
require_once '../controllers/controller.php';
$controller = new BlogController();
$data = $controller->handleRequest();

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default role for testing
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'visitor'; // Options: 'visitor', 'user', 'admin'
    $_SESSION['user_id'] = 1; // Default user ID for testing
}
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
        /* Facebook-like styling */
        .blog-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
        }
        
        .blog-post {
            background: rgba(255,255,255,0.9);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            color: #1e293b;
            transition: box-shadow 0.2s ease-in-out;
        }
        
        .blog-post:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .blog-post h2 {
            color: #0b2447;
            margin-bottom: 0.5rem;
            font-size: 1.6rem;
        }
        
        .blog-post-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .blog-post p {
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        
        .post-reactions {
            display: flex;
            gap: 1rem;
        }
        
        .reaction-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            background: #f1f5f9;
            border-radius: 20px;
            cursor: pointer;
            color: #64748b;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .reaction-btn:hover, .reaction-btn.active {
            background: #e2e8f0;
            color: #0b2447;
        }
        
        .reaction-btn i {
            font-size: 1.1rem;
        }
        
        .read-more {
            display: inline-block;
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .read-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }
        
        .post-creation-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 0 auto 2rem;
            max-width: 800px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .post-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .post-author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-weight: bold;
        }
        
        .post-input-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .post-title-input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            color: #0f172a;
            outline: none;
        }
        
        .post-input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 1rem;
            min-height: 80px;
            resize: none;
            color: #334155;
            line-height: 1.5;
            padding: 0.5rem;
        }
        
        .post-actions-bar {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            margin-top: 1rem;
        }
        
        .post-action-btns {
            display: flex;
            gap: 1rem;
        }
        
        .btn-attach {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            background: #f1f5f9;
            border-radius: 20px;
            cursor: pointer;
            color: #64748b;
            font-weight: 500;
        }
        
        .btn-attach:hover {
            background: #e2e8f0;
        }
        
        .btn-post {
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-post:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
        }
        
        .btn-post:not(:disabled):hover {
            background: linear-gradient(135deg, #0073e6, #0088b3);
            transform: translateY(-2px);
        }
        
        .no-posts-message {
            text-align: center;
            color: white;
            background: rgba(15, 23, 42, 0.7);
            padding: 2rem;
            border-radius: 12px;
            margin: 3rem auto;
            max-width: 600px;
        }
        
        .role-switcher {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
            border-radius: 20px;
            padding: 0.5rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 1rem auto;
            max-width: 800px;
        }
        
        .role-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 15px;
            background: transparent;
            color: white;
            cursor: pointer;
        }
        
        .role-btn.active {
            background: rgba(255,255,255,0.2);
            font-weight: bold;
        }
        
        .loader {
            display: flex;
            justify-content: center;
            padding: 2rem;
        }
        
        .loader::after {
            content: "";
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        .validation-error {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: -0.75rem;
            margin-bottom: 0.75rem;
            display: none;
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

    <!-- Role switcher for testing -->
    <div class="role-switcher">
        <span style="color:white">Testing: </span>
        <button class="role-btn <?= $_SESSION['role'] === 'visitor' ? 'active' : '' ?>" 
                onclick="switchRole('visitor')">Visitor</button>
        <button class="role-btn <?= $_SESSION['role'] === 'user' ? 'active' : '' ?>" 
                onclick="switchRole('user')">User</button>
        <button class="role-btn <?= $_SESSION['role'] === 'admin' ? 'active' : '' ?>" 
                onclick="switchRole('admin')">Admin</button>
    </div>

    <div class="blog-container">
        <?php if ($_SESSION['role'] !== 'visitor'): ?>
        <div class="post-creation-card">
            <div class="post-author">
                <div class="post-author-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <strong><?= $_SESSION['role'] === 'admin' ? 'Admin' : 'User' ?></strong>
                </div>
            </div>
            <div class="post-input-wrapper">
                <input 
                    type="text" 
                    id="postTitle"
                    placeholder="Enter post title..."
                    class="post-title-input"
                    onkeyup="validatePost()"
                    aria-describedby="titleError"
                >
                <div id="titleError" class="validation-error">Title cannot be empty.</div>
                <textarea 
                    class="post-input" 
                    placeholder="What's on your mind today? (min 10 chars)"
                    id="postContent"
                    onkeyup="validatePost()"
                    aria-describedby="contentError"
                ></textarea>
                <div id="contentError" class="validation-error">Content must be at least 10 characters long.</div>
            </div>
            <div class="post-actions-bar">
                <div class="post-action-btns">
                    <button class="btn-attach">
                        <i class="fas fa-image"></i> Photo
                    </button>
                </div>
                <button 
                    id="postButton" 
                    onclick="submitPost()" 
                    class="btn-post" 
                    disabled
                >Post</button>
            </div>
        </div>
        <?php endif; ?>
        
        <div id="posts-container">
            <div class="loader"></div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        // DOM loaded
        document.addEventListener('DOMContentLoaded', function() {
            loadPosts();
        });

        // Validation function with UI feedback
        function validatePost() {
            const titleInput = document.getElementById('postTitle');
            const contentInput = document.getElementById('postContent');
            const postButton = document.getElementById('postButton');
            const titleError = document.getElementById('titleError');
            const contentError = document.getElementById('contentError');
            
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            let isValid = true;

            if (!title) {
                titleError.style.display = 'block';
                isValid = false;
            } else {
                titleError.style.display = 'none';
            }

            if (content.length < 10) {
                contentError.style.display = 'block';
                isValid = false;
            } else {
                contentError.style.display = 'none';
            }
            
            postButton.disabled = !isValid;
        }

        // Post submission with better feedback
        function submitPost() {
            const titleInput = document.getElementById('postTitle');
            const contentInput = document.getElementById('postContent');
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            
            // Re-validate before submitting
            validatePost();
            if (document.getElementById('postButton').disabled) {
                showToast('Please fix the errors before posting.', true);
                return;
            }
            
            // Disable button during submission
            const postButton = document.getElementById('postButton');
            postButton.disabled = true;
            postButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            
            fetch('blog_backend.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ajax: true,
                    action: 'create',
                    title: title,
                    content: content
                })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data.error) {
                    showToast(data.error, true);
                } else if (data.success) {
                    titleInput.value = ''; // Clear inputs
                    contentInput.value = '';
                    showToast('Post published successfully!');
                    loadPosts(); // Reload posts to show the new one instantly
                    validatePost(); // Reset validation state
                } else {
                    showToast('An unexpected error occurred.', true);
                }
            })
            .catch(err => {
                showToast('Error submitting post. Check console for details.', true);
                console.error('Submission error:', err);
            })
            .finally(() => {
                // Re-enable button regardless of success/failure
                // Keep it disabled if validation fails after clearing inputs
                validatePost(); 
                if (!postButton.disabled) {
                     postButton.innerHTML = 'Post';
                }
            });
        }

        // Load all posts (minor refinement to add new post to top)
        function loadPosts() {
            const container = document.getElementById('posts-container');
            // Keep existing posts while loading new ones for a smoother feel?
            // Or show loader:
            container.innerHTML = '<div class="loader"></div>'; 
            
            fetch('blog_backend.php?action=list')
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                         throw new Error(data.error);
                    }
                    if (data.posts && data.posts.length > 0) {
                        // Sort posts by date descending (newest first) just in case backend didn't
                        data.posts.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                        
                        container.innerHTML = data.posts.map(post => `
                            <div class="blog-post" data-id="${post.id}">
                                <h2>${escapeHtml(post.title)}</h2>
                                <div class="blog-post-meta">
                                    <span>Posted on ${formatDate(post.created_at)} by User ${post.author_id}</span> 
                                    <span>${post.reactions} ${post.reactions === 1 ? 'reaction' : 'reactions'}</span>
                                </div>
                                <p>${escapeHtml(post.content.substring(0, 200))}${post.content.length > 200 ? '...' : ''}</p>
                                <div class="post-actions">
                                    <div class="post-reactions">
                                        <button class="reaction-btn" onclick="handleReaction(${post.id})">
                                            <i class="fas fa-thumbs-up"></i>
                                            <span id="reaction-count-${post.id}">${post.reactions}</span>
                                        </button>
                                        <button class="reaction-btn">
                                            <i class="fas fa-comment"></i>
                                            Comments
                                        </button>
                                    </div>
                                    <a href="post_details.php?id=${post.id}" class="read-more">
                                        <i class="fas fa-book-open"></i> Read More
                                    </a>
                                    ${isAdmin() ? `
                                    <button class="reaction-btn" onclick="deletePost(${post.id})">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>` : ''}
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = `
                            <div class="no-posts-message">
                                <h2><i class="fas fa-info-circle"></i> No posts yet</h2>
                                <p>Be the first to share something interesting!</p>
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    container.innerHTML = `
                        <div class="no-posts-message">
                            <h2><i class="fas fa-exclamation-triangle"></i> Error loading posts</h2>
                            <p>Could not fetch posts. Please try again later.</p>
                            <p style="font-size: 0.8em; color: #aaa;">${escapeHtml(err.message)}</p>
                        </div>
                    `;
                    console.error('Error loading posts:', err);
                });
        }

        // Handle reactions
        function handleReaction(postId) {
            if (isVisitor()) {
                showToast('Please login to react to posts', true);
                return;
            }
            
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
                    showToast(data.error, true);
                    return;
                }
                if (data.success) {
                    document.querySelector(`#reaction-count-${postId}`).textContent = data.count;
                    showToast('Reaction updated!');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error updating reaction', true);
            });
        }

        // Delete post (admin only)
        function deletePost(postId) {
            if (!isAdmin()) {
                showToast('Only admins can delete posts', true);
                return;
            }
            
            if (!confirm('Are you sure you want to delete this post?')) {
                return;
            }
            
            fetch('blog_backend.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ajax: true,
                    action: 'delete',
                    id: postId
                })
            })
            .then(res => {
                 if (!res.ok) { throw new Error(`HTTP error! status: ${res.status}`); }
                 return res.json();
            })
            .then(data => {
                if (data.error) {
                    showToast(data.error, true);
                } else if (data.success) {
                    showToast('Post deleted successfully');
                    // Remove post from DOM smoothly
                    const postElement = document.querySelector(`.blog-post[data-id="${postId}"]`);
                    if (postElement) {
                        postElement.style.transition = 'opacity 0.5s ease';
                        postElement.style.opacity = '0';
                        setTimeout(() => { 
                            postElement.remove();
                            // Check if container is empty after removal
                            if (!document.querySelector('#posts-container .blog-post')) {
                                loadPosts(); // Reload to show "No posts" message if needed
                            }
                        }, 500);
                    } else {
                         loadPosts(); // Fallback if element not found
                    }
                } else {
                    showToast('An unexpected error occurred during deletion.', true);
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                showToast('Error deleting post. Check console.', true);
            });
        }

        // Role switching for testing
        function switchRole(role) {
            fetch('blog_backend.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ajax: true,
                    action: 'switchRole',
                    role: role
                })
            })
            .then(() => {
                location.reload();
            });
        }

        // Helper functions
        function isVisitor() {
            return '<?= $_SESSION['role'] ?>' === 'visitor';
        }
        
        function isAdmin() {
            return '<?= $_SESSION['role'] ?>' === 'admin';
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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
</body>
</html>