<?php
require_once __DIR__ . '/../config/config.php';

class CommentModel {
    private $pdo;

    // Constructor now requires a PDO instance
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllComments() {
        return $this->pdo->query("SELECT c.*, u.name as author_name 
                                FROM comments c 
                                LEFT JOIN users u ON c.user_id = u.id 
                                ORDER BY c.created_at DESC")->fetchAll();
    }

    public function getCommentsByPostId($postId) {
        $stmt = $this->pdo->prepare("SELECT c.*, u.name as author_name 
                                   FROM comments c 
                                   LEFT JOIN users u ON c.user_id = u.id 
                                   WHERE c.post_id = :post_id 
                                   ORDER BY c.created_at DESC");
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public function addComment($postId, $content, $userId) {
        if ($userId === null || $userId === 0) {
            throw new Exception("User not logged in. Cannot add comment.");
        }
        
        $stmt = $this->pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $content
        ]);
        return $this->pdo->lastInsertId();
    }

    public function toggleCommentReaction($commentId, $userId) {
        if ($userId === null || $userId === 0) {
             throw new Exception("User must be logged in to react.");
        }
        
        $this->pdo->beginTransaction();
        try {
            // Check if reaction exists
            $stmt = $this->pdo->prepare("SELECT id FROM reactions_log WHERE user_id = :user_id AND item_id = :comment_id AND type = 'comment'");
            $stmt->execute(['user_id' => $userId, 'comment_id' => $commentId]);
            $existingReaction = $stmt->fetch();
            
            if ($existingReaction) {
                // Remove reaction from log
                $stmt = $this->pdo->prepare("DELETE FROM reactions_log WHERE id = :id");
                $stmt->execute(['id' => $existingReaction['id']]);
                
                // Decrement comment reactions count
                $stmt = $this->pdo->prepare("UPDATE comments SET reactions = GREATEST(0, reactions - 1) WHERE id = :comment_id");
                $stmt->execute(['comment_id' => $commentId]);
            } else {
                // Add reaction to log
                $stmt = $this->pdo->prepare("INSERT INTO reactions_log (user_id, type, item_id) VALUES (:user_id, 'comment', :comment_id)");
                $stmt->execute(['user_id' => $userId, 'comment_id' => $commentId]);
                
                // Increment comment reactions count
                $stmt = $this->pdo->prepare("UPDATE comments SET reactions = reactions + 1 WHERE id = :comment_id");
                $stmt->execute(['comment_id' => $commentId]);
            }
            
            // Commit transaction
            $this->pdo->commit();

            // Return new reaction count
            $stmt = $this->pdo->prepare("SELECT reactions FROM comments WHERE id = :comment_id");
            $stmt->execute(['comment_id' => $commentId]);
            return $stmt->fetchColumn();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            error_log("Comment reaction toggle failed: " . $e->getMessage());
            throw $e; // Re-throw exception to be caught by controller
        }
    }
}