<?php
require_once __DIR__ . '/../blog part/config/config.php';
require_once __DIR__ . '/../blog part/controllers/controller.php';

// Initialize the blog controller
$blogController = new BlogController();

// Get the latest 3 posts
$latestPosts = $blogController->getLatestPosts(3);
?>

<!-- Blog Section -->
<section class="blog-section">
    <h2>Latest Articles</h2>
    <div class="blog-preview">
        <?php if (!empty($latestPosts)): ?>
            <?php foreach ($latestPosts as $post): ?>
                <div class="blog-card">
                    <div class="blog-card-img"><i class="fas fa-blog"></i></div>
                    <div class="blog-card-content">
                        <h3 class="blog-card-title"><?= htmlspecialchars($post['title']) ?></h3>
                        <p class="blog-card-excerpt">
                            <?= htmlspecialchars(substr($post['content'], 0, 120)) ?>...
                        </p>
                        <div class="blog-card-meta">
                            <span><?= date('d M Y', strtotime($post['created_at'])) ?></span>
                            <span><?= $post['reactions'] ?> <i class="fas fa-thumbs-up"></i></span>
                        </div>
                        <a href="<?= BASE_URL ?>blog part/views/post_details.php?id=<?= $post['id'] ?>" class="blog-card-link">Read More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-posts-message">No blog posts available at the moment.</p>
        <?php endif; ?>
    </div>
    <div class="blog-view-all">
        <a href="<?= BASE_URL ?>blog part/views/blog_frontend.php" class="view-all-link">
            View All Articles <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>