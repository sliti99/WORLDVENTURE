<?php
require_once __DIR__ . '/../config/config.php';

class PostModel {
    private $pdo;

    // Constructor now requires a PDO instance
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllPosts() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
            if (!$stmt) {
                throw new Exception("Failed to execute query");
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Database error occurred");
        }
    }

    public function getPostById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getLatestPosts($limit = 3) {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createPost($title, $content, $authorId, $photoPath = null, $latitude = null, $longitude = null) {
        // Generate a simple slug (can be improved with a library)
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug); // Replace multiple hyphens
        
        // Ensure author_id is valid
        if ($authorId === null || $authorId === 0) {
            throw new Exception("User not logged in. Cannot create post.");
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO posts 
            (title, content, slug, author_id, status, photo_path, location_lat, location_lng) 
             VALUES (:title, :content, :slug, :author_id, :status, :photo_path, :location_lat, :location_lng)"
        );
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'slug' => $slug,
            'author_id' => $authorId,
            'status' => 'published',
            'photo_path' => $photoPath,
            'location_lat' => $latitude,
            'location_lng' => $longitude
        ]);
        
        return $this->pdo->lastInsertId();
    }

    public function deletePost($id) {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function toggleReaction($postId, $userId) {
        if ($userId === null || $userId === 0) {
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