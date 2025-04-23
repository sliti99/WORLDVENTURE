<?php
require_once '../controllers/controller.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
    <title><?php echo htmlspecialchars($post['title']); ?> - WorldVenture</title>
    <link rel="stylesheet" href="../../main_front/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .post-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            color: #1e293b;
        }
        
        .post-header {
            margin-bottom: 2rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
        }
        
        .post-header h1 {
            color: #0b2447;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .post-meta {
            color: #64748b;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
        }
        
        .post-content {
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .post-actions {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
            padding: 1rem 0;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .post-reaction {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f1f5f9;
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .post-reaction:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        
        .post-reaction.active {
            background: #0b2447;
            color: white;
        }
        
        .comments-section {
            margin-top: 3rem;
        }
        
        .comments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .comments-header h2 {
            color: #0b2447;
            font-size: 1.5rem;
        }
        
        .comment-list {
            list-style-type: none;
            padding: 0;
        }
        
        .comment {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 3px solid #3e92cc;
        }
        
        .comment-meta {
            display: flex;
            justify-content: space-between;
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        
        .comment-author {
            font-weight: 600;
            color: #0f172a;
        }
        
        .comment-form {
            margin-top: 2rem;
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 8px;
        }
        
        .comment-form h3 {
            margin-bottom: 1rem;
            color: #0b2447;
        }
        
        .comment-input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: linear-gradient(135deg, #0073e6, #0088b3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #3e92cc;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 2rem;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 3px solid #94a3b8;
            color: #334155;
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
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>
    
    <header>
        <img src="../../main_front/logo.png" alt="WorldVenture Logo" class="logo">
        <a href="../../main_front/index.html" class="login-btn">Return to Home</a>
    </header>

    <div class="post-container">
        <a href="blog_frontend.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Blog
        </a>
        
        <div class="post-header">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <span>Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                <span><?php echo $post['reactions']; ?> reactions</span>
            </div>
        </div>
        
        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
        
        <div class="post-actions">
            <button class="post-reaction" id="reaction-btn" onclick="handleReaction(<?php echo $post['id']; ?>)">
                <i class="fas fa-thumbs-up"></i>
                <span id="reaction-count"><?php echo $post['reactions']; ?></span> Like
            </button>
            
            <button class="post-reaction" onclick="document.getElementById('comment').focus()">
                <i class="fas fa-comment"></i> Comment
            </button>
            
            <button class="post-reaction" onclick="sharePost()">
                <i class="fas fa-share-alt"></i> Share
            </button>
        </div>
        
        <section class="comments-section">
            <div class="comments-header">
                <h2>
                    <i class="fas fa-comments"></i> 
                    Comments (<?php echo count($comments); ?>)
                </h2>
            </div>
            
            <?php if (count($comments) > 0): ?>
                <ul class="comment-list">
                    <?php foreach ($comments as $comment): ?>
                    <li class="comment">
                        <div class="comment-meta">
                            <span class="comment-author">User</span>
                            <span><?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?></span>
                        </div>
                        <div class="comment-text">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="alert">
                    <i class="fas fa-info-circle"></i> No comments yet. Be the first to comment!
                </div>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] !== 'visitor'): ?>
            <form id="commentForm" class="comment-form" method="POST" action="blog_backend.php">
                <h3><i class="fas fa-reply"></i> Add Your Comment</h3>
                <textarea 
                    id="comment" 
                    name="comment" 
                    class="comment-input" 
                    placeholder="Write your comment here..."
                    required
                ></textarea>
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                <input type="hidden" name="action" value="addComment">
                <button type="submit" class="btn-submit">Submit Comment</button>
            </form>
            <?php else: ?>
            <div class="alert">
                <i class="fas fa-lock"></i> You need to be logged in to post comments.
            </div>
            <?php endif; ?>
        </section>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const commentForm = document.getElementById('commentForm');
            if (commentForm) {
                commentForm.addEventListener('submit', function(event) {
                    const comment = document.getElementById('comment').value.trim();
                    
                    if (!comment) {
                        event.preventDefault();
                        showToast('Please write a comment before submitting', true);
                        return false;
                    }
                    
                    if (comment.length < 3) {
                        event.preventDefault();
                        showToast('Comment must be at least 3 characters long', true);
                        return false;
                    }
                });
            }
        });

        // Handle post reaction
        function handleReaction(postId) {
            <?php if ($_SESSION['role'] === 'visitor'): ?>
            showToast('Please login to react to posts', true);
            return;
            <?php endif; ?>
            
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
                    document.getElementById('reaction-count').textContent = data.count;
                    
                    // Toggle active class on button
                    const btn = document.getElementById('reaction-btn');
                    btn.classList.toggle('active');
                    
                    showToast('Reaction updated!');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error updating reaction', true);
            });
        }

        // Share post functionality
        function sharePost() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo htmlspecialchars(addslashes($post['title'])); ?>',
                    text: 'Check out this post on WorldVenture',
                    url: window.location.href
                })
                .catch(err => {
                    console.error('Share failed:', err);
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                showToast('Copy link: ' + window.location.href);
            }
        }

        // Toast notification
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