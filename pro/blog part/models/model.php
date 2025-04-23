<?php
class BlogModel {
    private $pdo;

    public function __construct() {
        // Ensure session is started for accessing $_SESSION['user_id']
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=worldventure", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Important for security
            ]);
        } catch (PDOException $e) {
            // Log error instead of dying in production
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later."); // User-friendly message
        }
    }

    public function getAllData() {
        return [
            'posts' => $this->pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll(),
            'comments' => $this->pdo->query("SELECT * FROM comments ORDER BY created_at DESC")->fetchAll()
        ];
    }

    public function getAllPosts() {
        // Join with users table to get author name (if needed later)
        // For now, just select from posts
        return $this->pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
    }

    public function getPostById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getCommentsByPostId($postId) {
        // Join with users table to get commenter name (if needed later)
        $stmt = $this->pdo->prepare("SELECT c.*, u.name as author_name 
                                     FROM comments c 
                                     LEFT JOIN users u ON c.user_id = u.id 
                                     WHERE c.post_id = :post_id 
                                     ORDER BY c.created_at DESC");
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public function getAllComments() {
        // Join with users table to get commenter name (if needed later)
        return $this->pdo->query("SELECT c.*, u.name as author_name 
                                 FROM comments c 
                                 LEFT JOIN users u ON c.user_id = u.id 
                                 ORDER BY c.created_at DESC")->fetchAll();
    }

    public function createPost($title, $content) {
        // Generate a simple slug (can be improved with a library)
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple hyphens
        
        // Ensure author_id is set from session
        $authorId = $_SESSION['user_id'] ?? null;
        if ($authorId === null) {
            // Handle error: User must be logged in to post
            // This should ideally be caught by the controller's permission check
            throw new Exception("User not logged in. Cannot create post.");
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO posts (title, content, slug, author_id, status) 
             VALUES (:title, :content, :slug, :author_id, :status)"
        );
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'slug' => $slug,
            'author_id' => $authorId, // Use session user ID
            'status' => 'published' // Default to published for simplicity
        ]);
        
        return $this->pdo->lastInsertId();
    }

    public function deletePost($id) {
        // Add check if user has permission (although controller should handle this)
        // Consider soft delete (setting a 'deleted_at' timestamp) instead of hard delete
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        // Optionally, delete related comments or handle via foreign key constraints
        // $stmt = $this->pdo->prepare("DELETE FROM comments WHERE post_id = :id");
        // $stmt->execute(['id' => $id]);
    }

    public function addComment($postId, $content) {
        // Ensure user_id is set from session
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            // Handle error: User must be logged in to comment
            throw new Exception("User not logged in. Cannot add comment.");
        }
        
        $stmt = $this->pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId, // Use session user ID
            'content' => $content
        ]);
        return $this->pdo->lastInsertId();
    }

    public function toggleReaction($postId, $userId) {
        if ($userId === null || $userId === 0) { // Ensure a valid user ID (not visitor)
             throw new Exception("User must be logged in to react.");
        }
        
        $this->pdo->beginTransaction();
        try {
            // Check if reaction exists
            $stmt = $this->pdo->prepare("SELECT id FROM reactions_log WHERE user_id = :user_id AND item_id = :post_id AND type = 'post'");
            $stmt->execute(['user_id' => $userId, 'post_id' => $postId]);
            $existingReaction = $stmt->fetch();
            
            if ($existingReaction) {
                // Remove reaction from log
                $stmt = $this->pdo->prepare("DELETE FROM reactions_log WHERE id = :id");
                $stmt->execute(['id' => $existingReaction['id']]);
                
                // Decrement post reactions count
                $stmt = $this->pdo->prepare("UPDATE posts SET reactions = GREATEST(0, reactions - 1) WHERE id = :post_id");
                $stmt->execute(['post_id' => $postId]);
            } else {
                // Add reaction to log
                $stmt = $this->pdo->prepare("INSERT INTO reactions_log (user_id, type, item_id) VALUES (:user_id, 'post', :post_id)");
                $stmt->execute(['user_id' => $userId, 'post_id' => $postId]);
                
                // Increment post reactions count
                $stmt = $this->pdo->prepare("UPDATE posts SET reactions = reactions + 1 WHERE id = :post_id");
                $stmt->execute(['post_id' => $postId]);
            }
            
            // Commit transaction
            $this->pdo->commit();

            // Return new reaction count
            $stmt = $this->pdo->prepare("SELECT reactions FROM posts WHERE id = :post_id");
            $stmt->execute(['post_id' => $postId]);
            return $stmt->fetchColumn();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            error_log("Reaction toggle failed: " . $e->getMessage());
            throw $e; // Re-throw exception to be caught by controller
        }
    }
}
?>