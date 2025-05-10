<?php
require_once '../controllers/controller.php';
require_once '../config/auth.php';

// Get post ID from URL parameter
$postId = $_GET['id'] ?? null;
if (!$postId) {
    header('Location: blog_frontend.php');
    exit;
}

// Initialize controller
$controller = new BlogController();

// Get post data
$post = $controller->getPostById($postId);
if (!$post) {
    header('Location: blog_frontend.php');
    exit;
}

// Get comments
$comments = $controller->getComments($postId);

// Handle comment submission
$commentError = '';
$commentSuccess = false;
$newCommentId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isLoggedIn()) {
        $commentError = 'You must be logged in to add a comment.';
    } else {
        $comment = trim($_POST['comment']);
        
        // Basic validation
        if (empty($comment)) {
            $commentError = 'Comment cannot be empty.';
        } else if (strlen($comment) < 3) {
            $commentError = 'Comment must be at least 3 characters long.';
        } else {
            try {
                // Add the comment
                $newCommentId = $controller->addComment($postId, $comment);
                $commentSuccess = true;
                
                // Redirect to avoid form resubmission
                header("Location: post_details.php?id=$postId&comment_success=true#comment-$newCommentId");
                exit;
            } catch (Exception $e) {
                $commentError = $e->getMessage();
            }
        }
    }
}

// Check if success message should be shown
$showSuccessMessage = isset($_GET['comment_success']) && $_GET['comment_success'] === 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - WorldVenture</title>
    <link rel="stylesheet" href="../../main_front/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/validation.js"></script>
    <style>
        /* Main styling */
        body {
            background: url('../../main_front/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
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
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background-color: rgba(0, 0, 0, 0.2);
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            height: 60px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #3e92cc;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: rgba(62, 146, 204, 0.1);
            margin-bottom: 2rem;
        }
        
        .back-link:hover {
            background: rgba(62, 146, 204, 0.2);
            transform: translateX(-3px);
        }
        
        /* Post styling */
        .post-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }
        
        .post-header {
            padding: 2rem;
            background: linear-gradient(135deg, #3e92cc, #0a4c8c);
            color: white;
        }
        
        .post-title {
            font-size: 2.4rem;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .post-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .author-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: white;
            color: #0a4c8c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .post-content {
            padding: 2rem;
            font-size: 1.1rem;
            line-height: 1.8;
        }
        
        .post-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            margin: 1rem 0;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Location map styling */
        .location-section {
            margin-top: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
        }
        
        .map-container {
            width: 100%;
            height: 300px;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Reactions styling */
        .reactions-section {
            padding: 1rem 2rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        /* Comments styling */
        .comments-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .comments-header {
            padding: 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .comments-title {
            font-size: 1.4rem;
            color: #0b2447;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .comments-count {
            background: #e0f2fe;
            color: #0ea5e9;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .comments-list {
            padding: 1rem;
        }
        
        .comment {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .comment-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #0b2447;
        }
        
        .comment-date {
            font-size: 0.85rem;
            color: #64748b;
        }
        
        .comment-content {
            color: #334155;
            margin-bottom: 0.5rem;
        }
        
        .comment-reactions {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .comment-form {
            padding: 1.5rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        
        .comment-input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 1rem;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        .comment-input:focus {
            border-color: #3e92cc;
            outline: none;
            box-shadow: 0 0 0 2px rgba(62, 146, 204, 0.2);
        }
        
        .submit-comment {
            background: linear-gradient(135deg, #3e92cc, #0a4c8c);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .submit-comment:hover {
            background: linear-gradient(135deg, #0073e6, #0088b3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(62, 146, 204, 0.4);
        }
        
        .login-prompt {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            color: #64748b;
        }
        
        .login-prompt a {
            color: #3e92cc;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .login-prompt a:hover {
            color: #0a4c8c;
            text-decoration: underline;
        }
        
        .success-message {
            background: #dcfce7;
            color: #10b981;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .error-message {
            background: #fee2e2;
            color: #ef4444;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            animation: fadeIn 0.5s;
        }
        
        #toast {
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
        
        #toast.show {
            transform: translateY(0);
        }
        
        #toast.error {
            background: #ef4444;
        }
    </style>
</head>
<body data-role="<?= getUserRole() ?>">
    <div class="background-image"></div>
    <div class="overlay"></div>
    
    <header>
        <a href="blog_frontend.php">
            <img src="../../main_front/logo.png" alt="WorldVenture Logo" class="logo">
        </a>
        <div>
            <?php if (isLoggedIn()): ?>
                <span style="margin-right: 1rem; font-weight: 500;">
                    <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?> 
                    <span class="badge <?= getUserRole() ?>"><?= ucfirst(getUserRole()) ?></span>
                </span>
                <a href="../config/logout.php" style="color: #ef4444; text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="login.php" style="color: #3e92cc; text-decoration: none; margin-right: 1rem;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="../../main_front/index.html" style="color: #64748b; text-decoration: none;">
                    <i class="fas fa-home"></i> Home
                </a>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="container">
        <a href="blog_frontend.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to All Posts
        </a>
        
        <div class="post-container">
            <div class="post-header">
                <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    <div class="post-author">
                        <div class="author-avatar">
                            <?= strtoupper(substr($post['author_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div>
                            <div><?= htmlspecialchars($post['author_name'] ?? 'Unknown User') ?></div>
                            <div><?= date('F j, Y', strtotime($post['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
                
                <?php if (!empty($post['photo_path'])): ?>
                    <img src="<?= htmlspecialchars($post['photo_path']) ?>" alt="Post Image" class="post-image">
                <?php endif; ?>
                
                <?php if (!empty($post['latitude']) && !empty($post['longitude'])): ?>
                    <div class="location-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                        <div class="map-container" id="map"></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="reactions-section">
                <button class="reaction-btn <?= $controller->hasUserReacted($post['id'], $_SESSION['user_id'] ?? 0, 'post') ? 'active' : '' ?>" id="reactionBtn" onclick="handleReaction()">
                    <i class="fas fa-thumbs-up"></i>
                    <span id="reactionCount"><?= $post['reactions'] ?? 0 ?></span> Like<?= ($post['reactions'] !== 1) ? 's' : '' ?>
                </button>
                
                <div>
                    <a href="#comments" class="reaction-btn">
                        <i class="fas fa-comment"></i> <?= count($comments) ?> Comment<?= (count($comments) !== 1) ? 's' : '' ?>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="comments-container" id="comments">
            <div class="comments-header">
                <h2 class="comments-title">
                    <i class="fas fa-comments"></i> Comments
                    <span class="comments-count"><?= count($comments) ?></span>
                </h2>
            </div>
            
            <?php if ($showSuccessMessage): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    Your comment has been added successfully!
                </div>
            <?php endif; ?>
            
            <?php if ($commentError): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($commentError) ?>
                </div>
            <?php endif; ?>
            
            <div class="comments-list">
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment" id="comment-<?= $comment['id'] ?>">
                            <div class="comment-header">
                                <div class="comment-author">
                                    <div class="author-avatar">
                                        <?= strtoupper(substr($comment['author_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div><?= htmlspecialchars($comment['author_name'] ?? 'Unknown User') ?></div>
                                </div>
                                <div class="comment-date"><?= date('F j, Y, g:i a', strtotime($comment['created_at'])) ?></div>
                            </div>
                            <div class="comment-content">
                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                            </div>
                            <div class="comment-reactions">
                                <button class="reaction-btn <?= $controller->hasUserReacted($comment['id'], $_SESSION['user_id'] ?? 0, 'comment') ? 'active' : '' ?>" onclick="handleCommentReaction(<?= $comment['id'] ?>)">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span id="comment-reaction-<?= $comment['id'] ?>"><?= $comment['reactions'] ?? 0 ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #64748b;">
                        <i class="fas fa-comment-slash" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <h3>No Comments Yet</h3>
                        <p>Be the first to share your thoughts!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="comment-form">
                <?php if (isLoggedIn()): ?>
                    <form method="POST" action="" id="commentForm">
                        <textarea 
                            name="comment" 
                            placeholder="Write your comment..." 
                            class="comment-input"
                            onkeyup="validateCommentForm()"
                            required
                        ></textarea>
                        <div class="error-message" style="display: none;"></div>
                        <button type="submit" class="submit-comment" disabled>
                            <i class="fas fa-paper-plane"></i> Post Comment
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p><i class="fas fa-lock"></i> Please <a href="login.php">log in</a> to leave a comment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div id="toast"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map if coordinates are available
            <?php if (!empty($post['latitude']) && !empty($post['longitude'])): ?>
            initMap(<?= $post['latitude'] ?>, <?= $post['longitude'] ?>);
            <?php endif; ?>
            
            // Set up form validation
            const commentForm = document.getElementById('commentForm');
            if (commentForm) {
                const commentInput = document.querySelector('.comment-input');
                const submitButton = document.querySelector('.submit-comment');
                const errorElement = document.querySelector('.error-message');
                
                commentInput.addEventListener('input', function() {
                    validateAndUpdateButton();
                });
                
                commentForm.addEventListener('submit', function(event) {
                    if (!validateCommentForm()) {
                        event.preventDefault();
                    }
                });
                
                function validateAndUpdateButton() {
                    const isValid = validateCommentForm();
                    submitButton.disabled = !isValid;
                }
            }
            
            // If there's a hash in the URL, scroll to it
            if (window.location.hash) {
                setTimeout(function() {
                    const element = document.querySelector(window.location.hash);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        element.style.animation = 'highlight 2s ease';
                    }
                }, 500);
            }
        });
        
        // Initialize Google Maps
        function initMap(lat, lng) {
            const mapDiv = document.getElementById('map');
            
            // Check if Google Maps API is loaded
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                loadGoogleMapsScript(() => {
                    createMap(lat, lng);
                });
            } else {
                createMap(lat, lng);
            }
        }
        
        function createMap(lat, lng) {
            const mapOptions = {
                zoom: 14,
                center: { lat: parseFloat(lat), lng: parseFloat(lng) },
                styles: [
                    {
                        "featureType": "water",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#e9e9e9" }, { "lightness": 17 }]
                    },
                    {
                        "featureType": "landscape",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#f5f5f5" }, { "lightness": 20 }]
                    },
                    {
                        "featureType": "road.highway",
                        "elementType": "geometry.fill",
                        "stylers": [{ "color": "#ffffff" }, { "lightness": 17 }]
                    },
                    {
                        "featureType": "road.highway",
                        "elementType": "geometry.stroke",
                        "stylers": [{ "color": "#ffffff" }, { "lightness": 29 }, { "weight": 0.2 }]
                    },
                    {
                        "featureType": "road.arterial",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#ffffff" }, { "lightness": 18 }]
                    },
                    {
                        "featureType": "road.local",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#ffffff" }, { "lightness": 16 }]
                    },
                    {
                        "featureType": "poi",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#f5f5f5" }, { "lightness": 21 }]
                    },
                    {
                        "featureType": "poi.park",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#dedede" }, { "lightness": 21 }]
                    },
                    {
                        "elementType": "labels.text.stroke",
                        "stylers": [{ "visibility": "on" }, { "color": "#ffffff" }, { "lightness": 16 }]
                    },
                    {
                        "elementType": "labels.text.fill",
                        "stylers": [{ "saturation": 36 }, { "color": "#333333" }, { "lightness": 40 }]
                    },
                    {
                        "elementType": "labels.icon",
                        "stylers": [{ "visibility": "off" }]
                    },
                    {
                        "featureType": "transit",
                        "elementType": "geometry",
                        "stylers": [{ "color": "#f2f2f2" }, { "lightness": 19 }]
                    },
                    {
                        "featureType": "administrative",
                        "elementType": "geometry.fill",
                        "stylers": [{ "color": "#fefefe" }, { "lightness": 20 }]
                    },
                    {
                        "featureType": "administrative",
                        "elementType": "geometry.stroke",
                        "stylers": [{ "color": "#fefefe" }, { "lightness": 17 }, { "weight": 1.2 }]
                    }
                ]
            };
            
            const map = new google.maps.Map(document.getElementById('map'), mapOptions);
            
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(lat), lng: parseFloat(lng) },
                map: map,
                title: '<?= addslashes($post['title']) ?>',
                animation: google.maps.Animation.DROP,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                    scaledSize: new google.maps.Size(40, 40)
                }
            });
        }
        
        function loadGoogleMapsScript(callback) {
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap';
            script.defer = true;
            script.async = true;
            document.body.appendChild(script);
            script.onload = callback;
        }
        
        // Handle post reactions
        function handleReaction() {
            if (isVisitor()) {
                showToast('Please login to like this post', true);
                return;
            }
            
            const postId = <?= $post['id'] ?>;
            const reactionBtn = document.getElementById('reactionBtn');
            const reactionCount = document.getElementById('reactionCount');
            
            // Add visual feedback immediately
            reactionBtn.classList.toggle('active');
            reactionBtn.disabled = true;
            
            fetch('../controllers/controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'react',
                    postId: postId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showToast(data.error, true);
                    reactionBtn.classList.toggle('active'); // Revert on error
                    return;
                }
                
                reactionCount.textContent = data.count;
                reactionBtn.classList.toggle('active', data.hasReacted);
                showToast('Your reaction has been recorded!');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating reaction', true);
                reactionBtn.classList.toggle('active'); // Revert on error
            })
            .finally(() => {
                reactionBtn.disabled = false;
            });
        }
        
        // Handle comment reactions
        function handleCommentReaction(commentId) {
            if (isVisitor()) {
                showToast('Please login to like this comment', true);
                return;
            }
            
            const reactionBtn = event.currentTarget;
            const reactionCount = document.getElementById(`comment-reaction-${commentId}`);
            
            // Add visual feedback immediately
            reactionBtn.classList.toggle('active');
            reactionBtn.disabled = true;
            
            fetch('../controllers/controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'reactToComment',
                    commentId: commentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showToast(data.error, true);
                    reactionBtn.classList.toggle('active'); // Revert on error
                    return;
                }
                
                reactionCount.textContent = data.count;
                showToast('Your reaction has been recorded!');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating reaction', true);
                reactionBtn.classList.toggle('active'); // Revert on error
            })
            .finally(() => {
                reactionBtn.disabled = false;
            });
        }
        
        // Show toast notification
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