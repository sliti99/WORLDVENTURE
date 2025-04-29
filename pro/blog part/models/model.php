<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/PostModel.php';
require_once __DIR__ . '/CommentModel.php';
require_once __DIR__ . '/Database.php'; // Include the Database Singleton

class BlogModel {
    private $pdo;
    private $postModel;
    private $commentModel;

    public function __construct() {
        // Ensure session is started for accessing $_SESSION['user_id']
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get PDO connection using the Singleton
        $this->pdo = Database::getInstance()->getConnection();
        
        // Pass the single PDO connection to the specific models
        $this->postModel = new PostModel($this->pdo);
        $this->commentModel = new CommentModel($this->pdo);
    }

    public function getAllData() {
        return [
            'posts' => $this->postModel->getAllPosts(),
            'comments' => $this->commentModel->getAllComments()
        ];
    }

    public function getAllPosts() {
        try {
            return $this->postModel->getAllPosts();
        } catch (Exception $e) {
            error_log("Error fetching posts: " . $e->getMessage());
            return false;
        }
    }

    public function getPostById($id) {
        return $this->postModel->getPostById($id);
    }

    public function getCommentsByPostId($postId) {
        return $this->commentModel->getCommentsByPostId($postId);
    }

    public function getAllComments() {
        return $this->commentModel->getAllComments();
    }

    public function createPost($title, $content) {
        // Ensure author_id is set from session
        $authorId = $_SESSION['user_id'] ?? null;
        return $this->postModel->createPost($title, $content, $authorId);
    }

    public function deletePost($id) {
        return $this->postModel->deletePost($id);
    }

    public function addComment($postId, $content) {
        // Ensure user_id is set from session
        $userId = $_SESSION['user_id'] ?? null;
        return $this->commentModel->addComment($postId, $content, $userId);
    }

    public function toggleReaction($postId, $userId) {
        return $this->postModel->toggleReaction($postId, $userId);
    }

    public function getLatestPosts($limit = 3) {
        return $this->postModel->getLatestPosts($limit);
    }

    public function toggleCommentReaction($commentId, $userId) {
        return $this->commentModel->toggleCommentReaction($commentId, $userId);
    }
    
    // New method to check if user has already reacted
    public function hasUserReacted($itemId, $userId, $type = 'post') {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM reactions_log 
             WHERE user_id = :user_id AND item_id = :item_id AND type = :type"
        );
        $stmt->execute([
            'user_id' => $userId,
            'item_id' => $itemId,
            'type' => $type
        ]);
        
        return (int)$stmt->fetchColumn() > 0;
    }
}
?>