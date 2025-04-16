<?php
class BlogModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=worldventure", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getAllData() {
        return [
            'posts' => $this->pdo->query("SELECT *, (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) as comment_count FROM posts ORDER BY created_at DESC")->fetchAll(),
            'comments' => $this->pdo->query("SELECT * FROM comments ORDER BY created_at DESC")->fetchAll()
        ];
    }

    public function getAllPosts() {
        return $this->pdo->query("SELECT p.*, (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) as comment_count FROM posts p ORDER BY p.created_at DESC")->fetchAll();
    }

    public function getPostById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getCommentsByPostId($postId) {
        $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE post_id = :post_id ORDER BY created_at DESC");
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public function getAllComments() {
        return $this->pdo->query("SELECT * FROM comments ORDER BY created_at DESC")->fetchAll();
    }

    public function createPost($title, $content, $authorId = 1) { // Accept authorId
        $stmt = $this->pdo->prepare("INSERT INTO posts (title, content, slug, author_id, status) VALUES (:title, :content, :slug, :author_id, :status)");
        // Basic slug generation, consider a more robust library/function if needed
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'slug' => $slug,
            'author_id' => $authorId, // Use provided authorId
            'status' => 'published'
        ]);
    }

    public function updatePost($id, $title, $content) {
        $stmt = $this->pdo->prepare("UPDATE posts SET title = :title, content = :content, slug = :slug WHERE id = :id");
         // Basic slug generation
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'slug' => $slug
        ]);
    }

    public function deletePost($id) {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE post_id = :id");
        $stmt->execute(['id' => $id]);

        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function deleteComment($id) {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function addComment($postId, $content, $userId = 1) { // Accept userId
        $stmt = $this->pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId, // Use provided userId
            'content' => $content
        ]);
    }

    public function incrementPostReaction($id, $userId) {
        try {
            // Verify userId is valid before proceeding
            if ($userId <= 0) {
                error_log("Invalid user ID ($userId) attempting to react to post $id");
                return false;
            }

            // First check if user has already reacted
            if ($this->hasUserReacted('post', $id, $userId)) {
                error_log("User $userId has already reacted to post $id");
                return false;
            }
            
            // Begin transaction for atomicity
            $this->pdo->beginTransaction();
            
            // Log the reaction first
            $stmt = $this->pdo->prepare("INSERT INTO reactions_log (user_id, type, item_id) VALUES (:user_id, :type, :item_id)");
            $stmt->execute([
                'user_id' => $userId,
                'type' => 'post',
                'item_id' => $id
            ]);
            
            // Then increment the post reaction count
            $stmt = $this->pdo->prepare("UPDATE posts SET reactions = reactions + 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            // Commit transaction
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            // Rollback on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            // Handle duplicate key errors silently (user already reacted)
            if ($e->getCode() == 23000) {
                error_log("Duplicate reaction attempt: User $userId on post $id");
                return false;
            }
            
            error_log("Database error in incrementPostReaction: " . $e->getMessage());
            return false;
        }
    }

    public function incrementCommentReaction($id, $userId) {
        try {
            // Verify userId is valid before proceeding
            if ($userId <= 0) {
                error_log("Invalid user ID ($userId) attempting to react to comment $id");
                return false;
            }

            // First check if user has already reacted
            if ($this->hasUserReacted('comment', $id, $userId)) {
                error_log("User $userId has already reacted to comment $id");
                return false;
            }
            
            // Begin transaction for atomicity
            $this->pdo->beginTransaction();
            
            // Log the reaction first
            $stmt = $this->pdo->prepare("INSERT INTO reactions_log (user_id, type, item_id) VALUES (:user_id, :type, :item_id)");
            $stmt->execute([
                'user_id' => $userId,
                'type' => 'comment',
                'item_id' => $id
            ]);
            
            // Then increment the comment reaction count
            $stmt = $this->pdo->prepare("UPDATE comments SET reactions = reactions + 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            // Commit transaction
            $this->pdo->commit();
            return true;
            
        } catch (PDOException $e) {
            // Rollback on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            // Handle duplicate key errors silently (user already reacted)
            if ($e->getCode() == 23000) {
                error_log("Duplicate reaction attempt: User $userId on comment $id");
                return false;
            }
            
            error_log("Database error in incrementCommentReaction: " . $e->getMessage());
            return false;
        }
    }

    // Improved reaction tracking system
    public function hasUserReacted($type, $id, $userId) {
        try {
            // Make sure the reactions_log table exists
            $this->ensureReactionsLogTableExists();
            
            if ($userId <= 0) {
                return false; // Non-logged in users can't react
            }
            
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log 
                                        WHERE type = :type AND item_id = :id AND user_id = :user_id");
            $stmt->execute([
                'type' => $type,
                'id' => $id,
                'user_id' => $userId
            ]);
            
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            // Log error and return false instead of crashing
            error_log("Error checking reactions: " . $e->getMessage());
            return false;
        }
    }
    
    // Ensures the reactions_log table exists
    private function ensureReactionsLogTableExists() {
        try {
            $this->pdo->query("SELECT 1 FROM reactions_log LIMIT 1");
        } catch (PDOException $e) {
            // Table doesn't exist, create it with proper structure
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS reactions_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type VARCHAR(20) NOT NULL,
                item_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_reaction (user_id, type, item_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");
        }
    }
    
    private function logReaction($type, $itemId, $userId) {
        try {
            $this->ensureReactionsLogTableExists();
            
            // Skip logging for invalid users
            if ($userId <= 0) {
                return false;
            }
            
            // First check if reaction already exists
            if ($this->hasUserReacted($type, $itemId, $userId)) {
                return false; // Already reacted
            }
            
            $stmt = $this->pdo->prepare("INSERT INTO reactions_log (type, item_id, user_id) 
                                        VALUES (:type, :item_id, :user_id)");
            $stmt->execute([
                'type' => $type,
                'item_id' => $itemId,
                'user_id' => $userId
            ]);
            return true;
        } catch (PDOException $e) {
            // If duplicate entry, silently ignore (user already reacted)
            if ($e->getCode() != 23000) { // 23000 is the error code for duplicate entry
                throw $e;
            }
            return false;
        }
    }
}
?>