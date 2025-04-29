<?php
require_once '../controllers/controller.php';
require_once '../config/auth.php';

$controller = new BlogController();

// Handle guest mode
if (isset($_GET['guest']) && $_GET['guest'] === 'true') {
    // Clear any existing session data for clean guest experience
    $_SESSION = [];
    $_SESSION['role'] = 'visitor';
    $_SESSION['user_id'] = 0;
    $_SESSION['logged_in'] = false;
}

// If not logged in and not in guest mode, redirect to login
if (!isLoggedIn() && !isset($_GET['guest'])) {
    header('Location: login.php');
    exit;
}

$data = $controller->handleRequest();
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
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            padding: 1.8rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            color: #1e293b;
            transition: all 0.3s ease;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .blog-post:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            transform: translateY(-3px);
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
            box-shadow: 0 2px 15px rgba(0,0,0,0.15);
            margin: 0 auto 2rem;
            max-width: 800px;
            padding: 1.5rem;
            transition: box-shadow 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        
        .post-creation-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .post-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .post-author-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #3e92cc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: transform 0.3s ease;
        }
        
        .post-author-avatar:hover {
            transform: scale(1.1);
        }
        
        .post-input-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .post-input-wrapper:focus-within {
            border-color: #3e92cc;
            box-shadow: 0 0 0 3px rgba(62, 146, 204, 0.2);
        }
        
        .post-title-input {
            width: 100%;
            border: none;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
            color: #0f172a;
            outline: none;
            transition: border-color 0.3s ease;
        }
        
        .post-title-input:focus {
            border-color: #3e92cc;
        }
        
        .post-input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 1.05rem;
            min-height: 100px;
            resize: none;
            color: #334155;
            line-height: 1.6;
            padding: 0.5rem;
            transition: height 0.3s ease;
        }
        
        .post-input:focus {
            height: 120px;
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
            padding: 0.6rem 1.2rem;
            border: none;
            background: #f1f5f9;
            border-radius: 20px;
            cursor: pointer;
            color: #64748b;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-attach:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
            color: #0f172a;
        }
        
        .btn-post {
            background: linear-gradient(135deg, #3e92cc, #0a4c8c);
            color: white;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-post:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-post:not(:disabled):hover {
            background: linear-gradient(135deg, #0073e6, #0088b3);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(62, 146, 204, 0.4);
        }
        
        .btn-post.ready {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(62, 146, 204, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(62, 146, 204, 0); }
            100% { box-shadow: 0 0 0 0 rgba(62, 146, 204, 0); }
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
        
        .user-menu {
            position: relative;
            display: inline-block;
        }
        
        .user-avatar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            cursor: pointer;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .user-avatar:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            min-width: 180px;
            z-index: 10;
            overflow: hidden;
            opacity: 0;
            transform: translateY(10px);
            pointer-events: none;
            transition: all 0.3s ease;
        }
        
        .user-dropdown.active {
            opacity: 1;
            transform: translateY(5px);
            pointer-events: auto;
        }
        
        .user-dropdown a,
        .user-dropdown button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            color: #334155;
            text-decoration: none;
            font-size: 0.95rem;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .user-dropdown a:hover,
        .user-dropdown button:hover {
            background: #f1f5f9;
            color: #0b2447;
        }
        
        .user-dropdown hr {
            margin: 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }
        
        .dropdown-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #0b2447;
            background: #f8fafc;
        }
        
        .user-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            color: white;
            margin-left: 0.5rem;
        }
        
        .user-badge.admin {
            background: #0ea5e9;
        }
        
        .user-badge.user {
            background: #10b981;
        }
        
        .user-badge.visitor {
            background: #94a3b8;
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
        
        .validation-error {
            color: #ef4444;
            font-size: 0.9rem;
            margin-top: 0.3rem;
            margin-bottom: 1rem;
            padding-left: 0.5rem;
            display: none;
            animation: fadeIn 0.3s ease-in;
            border-left: 3px solid #ef4444;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        input.error, textarea.error {
            border-color: #ef4444 !important;
            background-color: rgba(254, 226, 226, 0.3);
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
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem; margin-left: 5px;"></i>
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
                <a href="../../main_front/index.html" class="login-btn">Return to Home</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="blog-container">
        <?php if (getUserRole() !== 'visitor'): ?>
        <!-- Enhanced Facebook-like post creation card -->
        <div class="post-creation-card">
            <div class="post-author">
                <div class="post-author-avatar">
                    <?php 
                    $userInitial = isset($_SESSION['name']) ? strtoupper(substr($_SESSION['name'], 0, 1)) : '<i class="fas fa-user"></i>';
                    echo $userInitial;
                    ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($_SESSION['name'] ?? (getUserRole() === 'admin' ? 'Admin' : 'User')) ?></strong>
                    <div style="font-size: 0.8rem; color: #64748b;">Share your thoughts with the community</div>
                </div>
            </div>
            <div class="post-input-wrapper">
                <input 
                    type="text" 
                    id="postTitle"
                    placeholder="What's on your mind today?"
                    class="post-title-input"
                    onkeyup="validatePost()"
                    aria-describedby="titleError"
                >
                <div id="titleError" class="validation-error">Title cannot be empty.</div>
                <textarea 
                    class="post-input" 
                    placeholder="Share something with the community... (min 10 chars)"
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
                    <button class="btn-attach">
                        <i class="fas fa-video"></i> Video
                    </button>
                    <button class="btn-attach">
                        <i class="fas fa-map-marker-alt"></i> Location
                    </button>
                </div>
                <button 
                    id="postButton" 
                    onclick="submitPost()" 
                    class="btn-post" 
                    disabled
                ><i class="fas fa-paper-plane"></i> Share Post</button>
            </div>
        </div>
        <?php else: ?>
        <!-- Message for visitors -->
        <div style="text-align: center; margin: 2rem 0; background: rgba(255,255,255,0.8); padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <i class="fas fa-info-circle" style="font-size: 2rem; color: #3e92cc; margin-bottom: 1rem;"></i>
            <h3>You're browsing as a visitor</h3>
            <p>Log in to create posts, comment, and react to content.</p>
            <a href="login.php" class="btn-post" style="display: inline-block; margin-top: 1rem;">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </a>
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

        // Enhanced validation function with live feedback
        function validatePost() {
            const titleInput = document.getElementById('postTitle');
            const contentInput = document.getElementById('postContent');
            const postButton = document.getElementById('postButton');
            const titleError = document.getElementById('titleError');
            const contentError = document.getElementById('contentError');
            
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            let isValid = true;

            // Title validation with enhanced feedback
            if (!title) {
                titleError.textContent = 'Please provide a title for your post.';
                titleError.style.display = 'block';
                titleInput.classList.add('error');
                isValid = false;
            } else if (title.length < 3) {
                titleError.textContent = `Title should be at least 3 characters (${3 - title.length} more needed).`;
                titleError.style.display = 'block';
                titleInput.classList.add('error');
                isValid = false;
            } else {
                titleError.style.display = 'none';
                titleInput.classList.remove('error');
            }

            // Content validation with enhanced feedback
            if (!content) {
                contentError.textContent = 'Please write something in your post.';
                contentError.style.display = 'block';
                contentInput.classList.add('error');
                isValid = false;
            } else if (content.length < 10) {
                contentError.textContent = `Add ${10 - content.length} more character${content.length === 9 ? '' : 's'} to continue.`;
                contentError.style.display = 'block';
                contentInput.classList.add('error');
                isValid = false;
            } else {
                contentError.style.display = 'none';
                contentInput.classList.remove('error');
            }
            
            // Update button state with visual feedback
            postButton.disabled = !isValid;
            
            // Visual feedback on button
            if (isValid) {
                postButton.classList.add('ready');
                postButton.innerHTML = '<i class="fas fa-paper-plane"></i> Share Post';
            } else {
                postButton.classList.remove('ready');
                postButton.innerHTML = '<i class="fas fa-paper-plane"></i> Share Post';
            }
            
            return isValid;
        }

        // Enhanced post submission with better feedback
        function submitPost() {
            const titleInput = document.getElementById('postTitle');
            const contentInput = document.getElementById('postContent');
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            
            // Re-validate before submitting
            if (!validatePost()) {
                showToast('<i class="fas fa-exclamation-circle"></i> Please fix the errors before posting.', true);
                return;
            }
            
            // Disable button during submission with loading state
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
                    showToast(`<i class="fas fa-exclamation-circle"></i> ${data.error}`, true);
                } else if (data.success) {
                    // Add animation before clearing inputs
                    const postCreationCard = document.querySelector('.post-creation-card');
                    postCreationCard.style.transition = 'all 0.5s ease';
                    postCreationCard.style.backgroundColor = 'rgba(240, 253, 244, 0.9)';
                    postCreationCard.style.boxShadow = '0 0 15px rgba(16, 185, 129, 0.5)';
                    
                    setTimeout(() => {
                        titleInput.value = ''; // Clear inputs
                        contentInput.value = '';
                        showToast('<i class="fas fa-check-circle"></i> Your post has been published successfully!');
                        
                        // Reset card style after a brief delay
                        setTimeout(() => {
                            postCreationCard.style.backgroundColor = '';
                            postCreationCard.style.boxShadow = '';
                        }, 1000);
                        
                        // Add new post with animation at the top
                        loadPosts(true);
                        validatePost(); // Reset validation state
                    }, 500);
                } else {
                    showToast('<i class="fas fa-exclamation-triangle"></i> An unexpected error occurred.', true);
                }
            })
            .catch(err => {
                showToast('<i class="fas fa-bug"></i> Error submitting post. Check console for details.', true);
                console.error('Submission error:', err);
            })
            .finally(() => {
                // Re-enable button regardless of success/failure
                postButton.disabled = false;
                postButton.innerHTML = '<i class="fas fa-paper-plane"></i> Share Post';
                validatePost(); // Recheck validation state
            });
        }

        // Enhanced posts loading with smooth transitions and new post animation
        function loadPosts(addedNewPost = false) {
            const container = document.getElementById('posts-container');
            
            // Keep existing posts if just added a new one
            if (!addedNewPost) {
                container.innerHTML = '<div class="loader"></div>'; 
            }
            
            fetch('blog_backend.php?action=list', {
                headers: { // Add this headers block
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => {
                    if (!res.ok) {
                        // Try to get more info from the response body if it's not JSON
                        return res.text().then(text => {
                            throw new Error(`HTTP error! status: ${res.status}, Response: ${text.substring(0, 100)}...`);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                         throw new Error(data.error);
                    }
                    const postsContainer = document.getElementById('posts-container');
                    container.innerHTML = ''; // Clear loader
                    
                    if (!data.posts || data.posts.length === 0) {
                        container.innerHTML = `
                            <div class="no-posts-message">
                                <i class="fas fa-newspaper" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <h2>No Posts Yet</h2>
                                <p>Be the first to share something amazing with the community!</p>
                            </div>`;
                        return;
                    }
                    
                    // Create HTML for each post with animation
                    data.posts.forEach((post, index) => {
                        const postElement = document.createElement('div');
                        postElement.className = 'blog-post';
                        postElement.dataset.id = post.id;
                        postElement.style.animationDelay = `${index * 0.1}s`;
                        postElement.style.animation = 'fadeIn 0.5s ease forwards';
                        
                        // Check if this is a newly added post
                        const isNewPost = addedNewPost && index === 0;
                        if (isNewPost) {
                            postElement.className = 'blog-post new-post';
                        }
                        
                        const authorInitial = post.author_name ? post.author_name.charAt(0).toUpperCase() : 'U';
                        
                        postElement.innerHTML = `
                            <div class="post-author">
                                <div class="post-author-avatar">
                                    ${authorInitial}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a;">
                                        ${post.author_name || 'User ' + post.author_id}
                                    </div>
                                    <div style="font-size: 0.85rem; color: #64748b;">
                                        ${formatDate(post.created_at)}
                                    </div>
                                </div>
                            </div>
                            <h2>${escapeHtml(post.title)}</h2>
                            <div class="blog-post-meta">
                                <span><i class="fas fa-clock"></i> ${formatDate(post.created_at)}</span>
                                <span><i class="fas fa-tag"></i> WorldVenture Blog</span>
                            </div>
                            <p>${escapeHtml(post.content.substring(0, 280))}${post.content.length > 280 ? '...' : ''}</p>
                            <div class="post-actions">
                                <div class="post-reactions">
                                    <button class="reaction-btn" onclick="handleReaction(${post.id})">
                                        <i class="fas fa-thumbs-up"></i> <span id="reaction-count-${post.id}">${post.reactions}</span>
                                    </button>
                                    <button class="reaction-btn" onclick="window.location.href='post_details.php?id=${post.id}#comments'">
                                        <i class="fas fa-comment"></i> Comments
                                    </button>
                                </div>
                                <a href="post_details.php?id=${post.id}" class="read-more">Read More</a>
                            </div>
                            ${isAdmin() ? `
                            <div class="admin-actions" style="margin-top: 1rem; text-align: right;">
                                <button onclick="deletePost(${post.id})" class="reaction-btn" style="background-color: #fee2e2; color: #ef4444;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>` : ''}
                        `;
                        
                        container.appendChild(postElement);
                        
                        // Add highlight animation for new posts
                        if (isNewPost) {
                            setTimeout(() => {
                                postElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                postElement.style.animation = 'highlight 2s ease';
                            }, 300);
                        }
                    });
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

        // Enhanced reaction handling with better UX
        function handleReaction(postId) {
            if (isVisitor()) {
                showToast('<i class="fas fa-lock"></i> Please login to react to posts', true);
                return;
            }
            
            // Find and update UI before server response (optimistic UI update)
            const reactionBtn = document.querySelector(`.blog-post[data-id="${postId}"] .reaction-btn`);
            const countElement = document.getElementById(`reaction-count-${postId}`);
            
            if (reactionBtn) {
                reactionBtn.classList.add('active');
                reactionBtn.disabled = true; // Prevent multiple clicks
                
                // Add a temporary animation
                reactionBtn.style.transition = 'all 0.3s ease';
                reactionBtn.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    reactionBtn.style.transform = 'scale(1)';
                }, 300);
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
                    showToast(`<i class="fas fa-exclamation-circle"></i> ${data.error}`, true);
                    return;
                }
                if (data.success) {
                    if (countElement) {
                        // Animate the count change
                        countElement.style.transition = 'all 0.3s ease';
                        countElement.style.transform = 'scale(1.5)';
                        countElement.style.color = '#3e92cc';
                        
                        setTimeout(() => {
                            countElement.textContent = data.count;
                            setTimeout(() => {
                                countElement.style.transform = 'scale(1)';
                                countElement.style.color = '';
                            }, 300);
                        }, 100);
                    }
                    
                    showToast('<i class="fas fa-check-circle"></i> Your reaction has been recorded!');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('<i class="fas fa-times-circle"></i> Error updating reaction', true);
            })
            .finally(() => {
                if (reactionBtn) {
                    reactionBtn.disabled = false;
                }
            });
        }

        // Enhanced delete post functionality
        function deletePost(postId) {
            if (!isAdmin()) {
                showToast('<i class="fas fa-shield-alt"></i> Only admins can delete posts', true);
                return;
            }
            
            if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                return;
            }
            
            const postElement = document.querySelector(`.blog-post[data-id="${postId}"]`);
            if (postElement) {
                // Add visual feedback before deletion
                postElement.style.transition = 'all 0.5s ease';
                postElement.style.opacity = '0.5';
                postElement.style.transform = 'scale(0.95)';
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
                    showToast(`<i class="fas fa-exclamation-circle"></i> ${data.error}`, true);
                    // Restore the post element if there was an error
                    if (postElement) {
                        postElement.style.opacity = '1';
                        postElement.style.transform = 'scale(1)';
                    }
                } else if (data.success) {
                    showToast('<i class="fas fa-check-circle"></i> Post deleted successfully');
                    
                    // Remove post from DOM with animation
                    if (postElement) {
                        postElement.style.height = postElement.offsetHeight + 'px';
                        postElement.style.marginTop = '0';
                        postElement.style.marginBottom = '0';
                        
                        setTimeout(() => {
                            postElement.style.height = '0';
                            postElement.style.padding = '0';
                            postElement.style.margin = '0';
                            postElement.style.overflow = 'hidden';
                            
                            setTimeout(() => {
                                postElement.remove();
                                // Check if container is empty after removal
                                if (!document.querySelector('#posts-container .blog-post')) {
                                    loadPosts(); // Reload to show "No posts" message if needed
                                }
                            }, 500);
                        }, 100);
                    } else {
                         loadPosts(); // Fallback if element not found
                    }
                }
            })
            .catch(err => {
                console.error('Delete error:', err);
                showToast('<i class="fas fa-times-circle"></i> Error deleting post', true);
                // Restore the post element if there was an error
                if (postElement) {
                    postElement.style.opacity = '1';
                    postElement.style.transform = 'scale(1)';
                }
            });
        }

        // Helper functions
        function isVisitor() {
            return <?= json_encode(getUserRole() === 'visitor') ?>;
        }
        
        function isAdmin() {
            return <?= json_encode(getUserRole() === 'admin') ?>;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
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

        // Implementing the socket.io + Gemini filtering requirements for discussions
        const socketIoScript = document.createElement('script');
        socketIoScript.src = 'https://cdn.socket.io/4.6.0/socket.io.min.js';
        socketIoScript.integrity = 'sha384-c79GN5VsunZvi+Q/WObgk2in0CbZsHnjEqvFxC5DxHn9lTfNce2WW6h2pH6u/kF+';
        socketIoScript.crossOrigin = 'anonymous';
        document.head.appendChild(socketIoScript);

        // Add chat container to the blog UI
        document.addEventListener('DOMContentLoaded', function() {
            // Add the chat container to the page
            const chatContainer = document.createElement('div');
            chatContainer.className = 'chat-container';
            chatContainer.innerHTML = `
                <div class="chat-header">
                    <h3><i class="fas fa-comments"></i> WorldVenture Community Chat</h3>
                    <div class="chat-controls">
                        <button id="toggleChatBtn" class="btn-chat-toggle"><i class="fas fa-chevron-down"></i></button>
                    </div>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="welcome-message">
                        <i class="fas fa-globe-americas"></i>
                        <p>Welcome to the WorldVenture community chat! Share your travel experiences and connect with other travelers.</p>
                    </div>
                </div>
                <div class="chat-input-area">
                    <textarea 
                        id="chatInput" 
                        placeholder="Type your message here..." 
                        rows="2"
                        ${isVisitor() ? 'disabled' : ''}
                    ></textarea>
                    <button 
                        id="sendMessageBtn" 
                        class="btn-send-message"
                        ${isVisitor() ? 'disabled' : ''}
                    >
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                ${isVisitor() ? '<div class="visitor-message-chat">Please <a href="login.php">login</a> to participate in the chat</div>' : ''}
            `;
            document.body.appendChild(chatContainer);

            // Socket.io implementation
            socketIoScript.onload = function() {
                initializeChat();
            };

            // Initialize chat functionality
            function initializeChat() {
                // Connect to the chat server
                const socket = io('http://localhost:3000', {
                    withCredentials: true,
                    extraHeaders: {
                        "Access-Control-Allow-Origin": "*"
                    }
                });

                const chatMessages = document.getElementById('chatMessages');
                const chatInput = document.getElementById('chatInput');
                const sendMessageBtn = document.getElementById('sendMessageBtn');
                const toggleChatBtn = document.getElementById('toggleChatBtn');

                // Toggle chat visibility
                toggleChatBtn.addEventListener('click', function() {
                    const chatMessagesEl = document.query
                    if (chatMessagesEl.style.display === 'none') {
                        chatMessagesEl.style.display = 'flex';
                        chatInputArea.style.display = 'flex';
                        icon.className = 'fas fa-chevron-down';
                    } else {
                        chatMessagesEl.style.display = 'none';
                        chatInputArea.style.display = 'none';
                        icon.className = 'fas fa-chevron-up';
                    }
                });

                // Send message when button is clicked
                sendMessageBtn.addEventListener('click', sendMessage);

                // Send message when Enter key is pressed (but Shift+Enter for new line)
                chatInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });

                function sendMessage() {
                    const message = chatInput.value.trim();
                    if (!message) return;
                    
                    // Don't allow visitors to send messages
                    if (isVisitor()) {
                        showToast('<i class="fas fa-lock"></i> Please login to participate in the chat', true);
                        return;
                    }

                    // Send message to server
                    socket.emit('send_message', {
                        message,
                        user: {
                            id: <?= $_SESSION['user_id'] ?? 0 ?>,
                            name: '<?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?>',
                            role: '<?= getUserRole() ?>'
                        },
                        timestamp: new Date().toISOString()
                    });

                    // Clear input
                    chatInput.value = '';
                }

                // Handle incoming messages
                socket.on('new_message', function(data) {
                    appendMessage(data);
                    // Auto-scroll to the latest message
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });

                // Handle error messages (for blocked content)
                socket.on('message_blocked', function() {
                    showToast('<i class="fas fa-exclamation-triangle"></i> Your message was blocked due to inappropriate content', true);
                });

                // Handle reconnection
                socket.on('reconnect', function() {
                    appendSystemMessage('Reconnected to chat server');
                });

                // Handle disconnect
                socket.on('disconnect', function() {
                    appendSystemMessage('Disconnected from chat server. Attempting to reconnect...');
                });

                // Append a chat message to the conversation
                function appendMessage(data) {
                    const messageElement = document.createElement('div');
                    messageElement.className = 'chat-message';
                    
                    // Highlight if it's the current user's message
                    if (data.user.id === <?= $_SESSION['user_id'] ?? 0 ?>) {
                        messageElement.classList.add('own-message');
                    }
                    
                    // Add admin styling if applicable
                    if (data.user.role === 'admin') {
                        messageElement.classList.add('admin-message');
                    }
                    
                    const time = new Date(data.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    messageElement.innerHTML = `
                        <div class="message-header">
                            <span class="message-author">${escapeHtml(data.user.name)}</span>
                            ${data.user.role === 'admin' ? '<span class="message-badge admin">Admin</span>' : ''}
                            <span class="message-time">${time}</span>
                        </div>
                        <div class="message-content">${escapeHtml(data.message)}</div>
                    `;
                    
                    chatMessages.appendChild(messageElement);
                }
                
                // Append system messages
                function appendSystemMessage(text) {
                    const messageElement = document.createElement('div');
                    messageElement.className = 'chat-message system-message';
                    messageElement.innerHTML = `
                        <div class="message-content">
                            <i class="fas fa-info-circle"></i> ${text}
                        </div>
                    `;
                    chatMessages.appendChild(messageElement);
                }
            }
        });

        // Add chat styles
        const chatStyles = document.createElement('style');
        chatStyles.textContent = `
            .chat-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 320px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 5px 25px rgba(0,0,0,0.2);
                display: flex;
                flex-direction: column;
                z-index: 1000;
                overflow: hidden;
                max-height: 500px;
            }
            
            .chat-header {
                background: linear-gradient(135deg, #3e92cc, #0a4c8c);
                color: white;
                padding: 12px 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: pointer;
            }
            
            .chat-header h3 {
                margin: 0;
                font-size: 1rem;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .btn-chat-toggle {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                transition: transform 0.3s;
            }
            
            .btn-chat-toggle:hover {
                transform: translateY(2px);
            }
            
            .chat-messages {
                padding: 15px;
                height: 300px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 10px;
                background: #f8fafc;
            }
            
            .welcome-message {
                background: #e0f2fe;
                padding: 12px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 0.9rem;
                color: #0c4a6e;
                margin-bottom: 10px;
            }
            
            .welcome-message i {
                font-size: 1.5rem;
                color: #0284c7;
            }
            
            .chat-message {
                padding: 10px 12px;
                border-radius: 8px;
                max-width: 85%;
                background: #e2e8f0;
                align-self: flex-start;
                font-size: 0.9rem;
                animation: fadeIn 0.3s ease-out;
            }
            
            .chat-message.own-message {
                background: #dbeafe;
                align-self: flex-end;
            }
            
            .chat-message.admin-message {
                background: #bae6fd;
            }
            
            .chat-message.system-message {
                background: #fef3c7;
                align-self: center;
                color: #92400e;
                font-size: 0.8rem;
                padding: 6px 10px;
            }
            
            .message-header {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 5px;
                flex-wrap: wrap;
            }
            
            .message-author {
                font-weight: 600;
                color: #334155;
            }
            
            .message-badge {
                font-size: 0.7rem;
                padding: 2px 6px;
                border-radius: 10px;
                color: white;
                font-weight: 500;
            }
            
            .message-badge.admin {
                background: #0ea5e9;
            }
            
            .message-time {
                font-size: 0.75rem;
                color: #64748b;
                margin-left: auto;
            }
            
            .message-content {
                line-height: 1.4;
                color: #334155;
                overflow-wrap: break-word;
                word-break: break-word;
            }
            
            .chat-input-area {
                padding: 10px;
                border-top: 1px solid #e2e8f0;
                display: flex;
                gap: 8px;
                background: white;
            }
            
            #chatInput {
                flex-grow: 1;
                padding: 8px 12px;
                border: 1px solid #e2e8f0;
                border-radius: 20px;
                resize: none;
                outline: none;
                font-family: inherit;
                font-size: 0.9rem;
                transition: border-color 0.3s;
            }
            
            #chatInput:focus {
                border-color: #3e92cc;
            }
            
            #chatInput:disabled {
                background: #f1f5f9;
                cursor: not-allowed;
            }
            
            .btn-send-message {
                width: 36px;
                height: 36px;
                background: linear-gradient(135deg, #3e92cc, #0a4c8c);
                color: white;
                border: none;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .btn-send-message:hover {
                transform: translateY(-2px);
                box-shadow: 0 2px 8px rgba(10, 76, 140, 0.3);
            }
            
            .btn-send-message:disabled {
                background: #cbd5e1;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }
            
            .visitor-message-chat {
                background: #f1f5f9;
                color: #64748b;
                padding: 8px;
                text-align: center;
                font-size: 0.85rem;
                border-top: 1px solid #e2e8f0;
            }
            
            .visitor-message-chat a {
                color: #3e92cc;
                text-decoration: none;
                font-weight: 600;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(chatStyles);
    </script>
</body>
</html>