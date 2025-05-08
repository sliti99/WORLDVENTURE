<?php
// Ensure paths are correct for the current directory structure
require_once '../config/config.php';

// View-specific code starts here
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
    <!-- Replace problematic Font Awesome with a more reliable version -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/validation.js"></script>
    <script src="js/chat.js"></script>
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
            gap: 8px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.85);
            transition: all 0.2s;
        }
        
        .user-avatar:hover {
            background: white;
        }
        
        .user-avatar i {
            font-size: 1.2rem;
            color: var(--bleu);
        }
        
        .user-avatar .fa-chevron-down {
            font-size: 0.8rem;
            margin-left: 4px;
            transition: transform 0.3s;
        }
        
        .user-avatar.active .fa-chevron-down {
            transform: rotate(180deg);
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

        /* Chat Interface Styling */
        .chat-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            height: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .chat-header {
            padding: 10px 15px;
            background: #3e92cc;
            color: white;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        
        .chat-header .toggle-chat {
            transform: rotate(0deg);
            transition: transform 0.3s ease;
        }
        
        .chat-container.minimized {
            height: 40px;
        }
        
        .chat-container.minimized .toggle-chat {
            transform: rotate(180deg);
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .chat-message {
            padding: 8px 12px;
            border-radius: 18px;
            max-width: 70%;
            word-break: break-word;
        }
        
        .chat-message.mine {
            background: #3e92cc;
            color: white;
            align-self: flex-end;
        }
        
        .chat-message.other {
            background: #f1f0f0;
            color: #333;
            align-self: flex-start;
        }
        
        .chat-message .author {
            font-size: 0.75rem;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .chat-message.admin .author {
            color: #e74c3c;
        }
        
        .chat-input-wrapper {
            padding: 10px;
            border-top: 1px solid #eee;
            display: flex;
            background: white;
        }
        
        .chat-input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 8px 15px;
            outline: none;
        }
        
        .chat-input:focus {
            border-color: #3e92cc;
        }
        
        .send-btn {
            border: none;
            background: #3e92cc;
            color: white;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .send-btn:hover {
            background: #2c7cb8;
            transform: scale(1.05);
        }
        
        .send-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .chat-notification {
            position: absolute;
            right: 10px;
            top: 10px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            display: none;
        }
        
        /* Make sure the background matches the main site */
        body {
            background: url('../../main_front/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: rgba(255, 255, 255, 0.85);  /* Semi-transparent white overlay */
        }
        
        #toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(150%);
            background: #333;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            transition: transform 0.3s ease-out;
            z-index: 1001;
        }
        
        #toast.show {
            transform: translateX(-50%) translateY(0);
        }
        
        /* Error styling for form validation */
        .error {
            border-color: #e74c3c !important;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>
</head>
<body data-role="<?= htmlspecialchars(getUserRole()) ?>" data-user-id="<?= htmlspecialchars($_SESSION['user_id'] ?? 0) ?>">
    <div class="background-image"></div>
    <!-- Global hidden inputs for photo and geolocation -->
    <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none" onchange="handlePhotoSelect()">
    <input type="hidden" id="latitudeInput" name="latitude">
    <input type="hidden" id="longitudeInput" name="longitude">

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
        <!-- Enhanced Facebook-like post creation card with validation -->
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
                    onkeyup="validatePostForm()"
                    aria-describedby="titleError"
                >
                <div id="titleError" class="validation-error">Title cannot be empty.</div>
                <textarea 
                    class="post-input" 
                    placeholder="Share something with the community... (min 10 chars)"
                    id="postContent"
                    onkeyup="validatePostForm()"
                    aria-describedby="contentError"
                ></textarea>
                <div id="contentError" class="validation-error">Content must be at least 10 characters long.</div>
            </div>
            <div class="post-actions-bar">
                <div class="post-action-btns">
                    <button type="button" class="btn-attach" onclick="openPhotoDialog()">
                        <i class="fas fa-image"></i> Photo
                    </button>
                    <button type="button" class="btn-attach" onclick="captureLocation()">
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
        
        <div id="posts-container">
            <div class="loader"></div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <!-- Chat interface -->
    <div class="chat-container">
        <div class="chat-header">
            <span>WorldVenture Chat</span>
            <span class="toggle-chat">▼</span>
            <div class="chat-notification">0</div>
        </div>
        <div class="chat-messages" id="chatMessages">
            <!-- Chat messages will be loaded here -->
        </div>
        <div class="chat-input-wrapper">
            <input type="text" class="chat-input" id="chatInput" placeholder="Type a message..." autocomplete="off">
            <button class="send-btn" id="sendButton" disabled>
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script>
        // DOM loaded
        document.addEventListener('DOMContentLoaded', function() {
            loadPosts();
            
            // Initialize validation on page load
            validatePostForm();
            
            // Initialize chat
            loadChatMessages();
            
            // Toggle chat minimization
            document.querySelector('.toggle-chat').addEventListener('click', function() {
                document.querySelector('.chat-container').classList.toggle('minimized');
            });
            
            // Enable/disable send button based on input
            document.getElementById('chatInput').addEventListener('input', function() {
                document.getElementById('sendButton').disabled = !validateChatMessage(this.value);
            });
            
            // Send message on button click
            document.getElementById('sendButton').addEventListener('click', sendChatMessage);
            
            // Send message on Enter key
            document.getElementById('chatInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !this.disabled) {
                    sendChatMessage();
                }
            });
            
            // Load new messages periodically
            setInterval(loadChatMessages, 5000);
        });

        // Toggle user dropdown menu
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            const avatar = document.querySelector('.user-avatar');
            // Toggle dropdown visibility and avatar active state for chevron rotation
            dropdown.classList.toggle('active');
            avatar.classList.toggle('active');
             
            // Close dropdown and reset avatar when clicking outside
            document.addEventListener('click', function(event) {
                const userMenu = document.querySelector('.user-menu');
                const clickedOutside = !userMenu.contains(event.target);
                if (clickedOutside) {
                    dropdown.classList.remove('active');
                    avatar.classList.remove('active');
                }
            });
        }

        // Enhanced validation function now uses our validation.js library
        function validatePost() {
            validatePostForm(); // Call our external validation function
        }

        // Submit Post - Updated to use validation from validation.js
        async function submitPost() {
            // Validate the form before submission
            if (!validatePostForm()) {
                return;
            }
            
            // Get input values
            const titleInput = document.getElementById('postTitle');
            const contentInput = document.getElementById('postContent');
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            
            // Disable button during submission with loading state
            const postButton = document.getElementById('postButton');
            postButton.disabled = true;
            postButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
            
            // Prepare form data for multipart submission (incl. photo)
            const photoInputEl = document.getElementById('photoInput');
            if (!photoInputEl) {
                console.warn('Photo input element missing, proceeding without photo');
            }
            const formData = new FormData();
            formData.append('ajax', true);
            formData.append('action', 'create');
            formData.append('title', title);
            formData.append('content', content);
            if (photoInputEl && photoInputEl.files.length > 0) {
                formData.append('photo', photoInputEl.files[0]);
            }
            // Include geolocation if available
            const lat = document.getElementById('latitudeInput').value;
            const lng = document.getElementById('longitudeInput').value;
            if (lat && lng) {
                formData.append('latitude', lat);
                formData.append('longitude', lng);
            }
            // Debug: log formData entries
            for (let [key, val] of formData.entries()) {
                console.log('formData', key, val instanceof File ? val.name : val);
            }
            
            // Submit with timeout and async/await
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 15000); // 15s timeout
                const response = await fetch('blog_backend.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP ${response.status}: ${text}`);
                }
                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }
                // Success: animate and reload posts
                const postCreationCard = document.querySelector('.post-creation-card');
                postCreationCard.style.backgroundColor = 'rgba(240, 253, 244, 0.9)';
                postCreationCard.style.boxShadow = '0 0 15px rgba(16, 185, 129, 0.5)';
                setTimeout(() => {
                    titleInput.value = '';
                    contentInput.value = '';
                    const latEl = document.getElementById('latitudeInput'); if (latEl) latEl.value = '';
                    const lngEl = document.getElementById('longitudeInput'); if (lngEl) lngEl.value = '';
                    const photoEl = document.getElementById('photoInput'); if (photoEl) photoEl.value = '';
                    showToast('<i class="fas fa-check-circle"></i> Your post has been published successfully!');
                    setTimeout(() => {
                        postCreationCard.style.backgroundColor = '';
                        postCreationCard.style.boxShadow = '';
                    }, 800);
                    loadPosts(true);
                    validatePost();
                }, 500);
            } catch (err) {
                console.error('submitPost error:', err);
                const msg = (err.name === 'AbortError') 
                    ? 'Request timed out. Please try again.' 
                    : err.message;
                showToast(`<i class="fas fa-exclamation-circle"></i> ${msg}`, true);
            } finally {
                // Always reset button state
                postButton.disabled = false;
                postButton.innerHTML = '<i class="fas fa-paper-plane"></i> Share Post';
                validatePost();
            }
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
     ${post.photo_path ? `<img src="${post.photo_path}" alt="Post Image" style="max-width:100%;border-radius:8px;margin:1rem 0;">` : ''}
     ${post.location_lat && post.location_lng ? (() => {
         const lat = parseFloat(post.location_lat);
         const lng = parseFloat(post.location_lng);
         return (!isNaN(lat) && !isNaN(lng))
             ? `<div style="font-size:0.85rem;color:#64748b;margin-bottom:1rem;"><i class="fas fa-map-marker-alt"></i> ${lat.toFixed(4)}, ${lng.toFixed(4)}</div>`
             : '';
     })() : ''}
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

         // Enhanced chat functionality
         function loadChatMessages() {
             fetch('../controllers/chat_api.php?action=get_messages')
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         const chatMessages = document.getElementById('chatMessages');
                         chatMessages.innerHTML = ''; // Clear existing messages
                         
                         data.messages.forEach(message => {
                             const messageElement = document.createElement('div');
                             messageElement.className = 'chat-message';
                             messageElement.id = 'chat-msg-' + message.id;
                             
                             // Highlight if it's the current user's message
                             if (message.user_id == <?= $_SESSION['user_id'] ?? 0 ?>) {
                                 messageElement.classList.add('mine');
                             } else {
                                 messageElement.classList.add('other');
                             }
                             
                             // Add admin styling if applicable
                             if (message.user_role === 'admin') {
                                 messageElement.classList.add('admin');
                             }
                             
                             const timestamp = new Date(message.created_at);
                             const time = timestamp.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                             
                             messageElement.innerHTML = `
                                 <div class="message-header">
                                     <span class="message-author">${escapeHtml(message.user_name)}</span>
                                     ${message.user_role === 'admin' ? '<span class="message-badge admin">Admin</span>' : ''}
                                     <span class="message-time">${time}</span>
                                 </div>
                                 <div class="message-content">${escapeHtml(message.content)}</div>
                             `;
                             
                             chatMessages.appendChild(messageElement);
                         });
                         
                         // Auto-scroll to the bottom of the chat
                         chatMessages.scrollTop = chatMessages.scrollHeight;
                     }
                 })
                 .catch(error => {
                     console.error('Error loading chat messages:', error);
                 });
         }
         
         // Send a new chat message
         function sendChatMessage() {
             const message = document.getElementById('chatInput').value.trim();
             
             if (!validateChatMessage(message)) {
                 showToast('<i class="fas fa-exclamation-circle"></i> Please enter a message', true);
                 return;
             }
             
             if (isVisitor()) {
                 showToast('<i class="fas fa-lock"></i> Please login to participate in the chat', true);
                 return;
             }
             
             // Send message to server
             const formData = new FormData();
             formData.append('action', 'send_message');
             formData.append('message', message);
             
             fetch('../controllers/chat_api.php', {
                 method: 'POST',
                 body: formData
             })
             .then(response => response.json())
             .then(data => {
                 if (data.success) {
                     // Clear input
                     document.getElementById('chatInput').value = '';
                     
                     // Append the new message
                     appendChatMessage(data.message);
                     
                     // Scroll to bottom
                     document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
                 } else {
                     showToast('<i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Error sending message'), true);
                 }
             })
             .catch(error => {
                 console.error('Error sending chat message:', error);
                 showToast('<i class="fas fa-exclamation-circle"></i> Error sending message', true);
             });
         }
         
         // Append a message to the chat
         function appendChatMessage(data) {
             const messageElement = document.createElement('div');
             messageElement.className = 'chat-message';
             messageElement.id = 'chat-msg-' + data.id;
             
             // Highlight if it's the current user's message
             if (data.user_id == <?= $_SESSION['user_id'] ?? 0 ?>) {
                 messageElement.classList.add('mine');
             } else {
                 messageElement.classList.add('other');
             }
             
             // Add admin styling if applicable
             if (data.user_role === 'admin') {
                 messageElement.classList.add('admin');
             }
             
             const timestamp = new Date(data.created_at);
             const time = timestamp.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
             
             messageElement.innerHTML = `
                 <div class="message-header">
                     <span class="message-author">${escapeHtml(data.user_name)}</span>
                     ${data.user_role === 'admin' ? '<span class="message-badge admin">Admin</span>' : ''}
                     <span class="message-time">${time}</span>
                 </div>
                 <div class="message-content">${escapeHtml(data.content)}</div>
             `;
             
             document.getElementById('chatMessages').appendChild(messageElement);
         }

        // Photo dialog and handler
        function openPhotoDialog() {
             const input = document.getElementById('photoInput');
             if (!input) {
                 console.warn('Photo input element not found');
                 return;
             }
             input.click();
         }

         function handlePhotoSelect() {
             const file = document.getElementById('photoInput').files[0];
             if (file) {
                 showToast(`<i class="fas fa-image"></i> Selected: ${file.name}`);
             }
         }

         // Geolocation capture for post creation
         function captureLocation() {
             if (!navigator.geolocation) {
                 showToast('<i class="fas fa-exclamation-circle"></i> Geolocation is not supported by your browser', true);
                 return;
             }
             showToast('<i class="fas fa-spinner fa-spin"></i> Retrieving location...');
             navigator.geolocation.getCurrentPosition(
                 (pos) => {
                     const { latitude, longitude } = pos.coords;
                     const latEl = document.getElementById('latitudeInput'); if (latEl) latEl.value = latitude;
                     const lngEl = document.getElementById('longitudeInput'); if (lngEl) lngEl.value = longitude;
                     showToast(`<i class="fas fa-map-marker-alt"></i> Location captured (${latitude.toFixed(4)}, ${longitude.toFixed(4)})`);
                 },
                 (err) => {
                     showToast('<i class="fas fa-exclamation-triangle"></i> Unable to retrieve location', true);
                     console.error('Geolocation error:', err);
                 },
                 { enableHighAccuracy: true, timeout: 10000 }
             );
         }
    </script>
</body>
</html>