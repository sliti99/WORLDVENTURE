<?php
require_once '../config/config.php';
require_once '../models/CommentModel.php';

class CommentC {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Get all comments
    public function afficherComments() {
        $stmt = $this->pdo->query("SELECT c.*, u.name AS author_name FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.created_at ASC");
        $commentsData = $stmt->fetchAll();
        
        $comments = [];
        foreach ($commentsData as $commentData) {
            $comment = new Comment();
            $comment->setId($commentData['id']);
            $comment->setPostId($commentData['post_id']);
            $comment->setContent($commentData['content']);
            $comment->setUserId($commentData['user_id']);
            $comment->setCreatedAt($commentData['created_at']);
            $comment->setAuthorName($commentData['author_name']);
            
            // Get reaction count for this comment
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :commentId AND type = 'comment'");
            $stmt->execute(['commentId' => $comment->getId()]);
            $comment->setReactions($stmt->fetchColumn());
            
            $comments[] = $comment;
        }
        
        return $comments;
    }

    // Add a new comment
    public function ajouter($postId, $content, $userId) {
        $stmt = $this->pdo->prepare("INSERT INTO comments (post_id, content, user_id, created_at) VALUES (:postId, :content, :userId, NOW())");
        $stmt->execute([
            'postId' => $postId,
            'content' => $content,
            'userId' => $userId
        ]);
        
        return $this->pdo->lastInsertId();
    }

    // Get comments for a specific post with standardized French naming
    public function recupererCommentsByPostId($postId) {
        $stmt = $this->pdo->prepare("SELECT c.*, u.name AS author_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = :postId ORDER BY c.created_at ASC");
        $stmt->execute(['postId' => $postId]);
        $commentsData = $stmt->fetchAll();
        
        // Get reaction counts
        foreach ($commentsData as &$comment) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :commentId AND type = 'comment'");
            $stmt->execute(['commentId' => $comment['id']]);
            $comment['reactions'] = (int)$stmt->fetchColumn();
        }
        
        return $commentsData;
    }

    // Get a specific comment by ID
    public function detail($id) {
        $stmt = $this->pdo->prepare("SELECT c.*, u.name AS author_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = :id");
        $stmt->execute(['id' => $id]);
        $commentData = $stmt->fetch();
        
        if (!$commentData) {
            return null;
        }
        
        // Get reaction count
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :commentId AND type = 'comment'");
        $stmt->execute(['commentId' => $id]);
        $commentData['reactions'] = (int)$stmt->fetchColumn();
        
        return $commentData;
    }

    // Update a comment
    public function modifier($id, $content) {
        $stmt = $this->pdo->prepare("UPDATE comments SET content = :content WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'content' => $content
        ]);
        
        return $stmt->rowCount() > 0;
    }

    // Delete a comment
    public function supprimer($id) {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }
    
    // Track user reaction to a comment with French naming
    public function ajouterReaction($commentId, $userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='comment'");
        $stmt->execute(['userId' => $userId, 'itemId' => $commentId]);
        
        if ((int)$stmt->fetchColumn() > 0) {
            $stmt = $this->pdo->prepare("DELETE FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='comment'");
            $stmt->execute(['userId' => $userId, 'itemId' => $commentId]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO reactions_log (user_id, item_id, type, created_at) VALUES (:userId, :itemId, 'comment', NOW())");
            $stmt->execute(['userId' => $userId, 'itemId' => $commentId]);
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id=:itemId AND type='comment'");
        $stmt->execute(['itemId' => $commentId]);
        $count = (int)$stmt->fetchColumn();
        
        return $count;
    }
    
    // Check if user has reacted to a comment - standardized French naming
    public function verifierReaction($itemId, $userId) {
        if (!$userId || $userId === 0) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id = :userId AND item_id = :itemId AND type = 'comment'");
        $stmt->execute([
            'userId' => $userId,
            'itemId' => $itemId
        ]);
        
        return (int)$stmt->fetchColumn() > 0;
    }
    
    // Handle comment reaction with standardized French naming
    public function gererReaction($commentId, $userId) {
        return $this->ajouterReaction($commentId, $userId);
    }
    
    // Aliases for backward compatibility - will be deprecated
    public function hasUserReacted($itemId, $userId) {
        return $this->verifierReaction($itemId, $userId);
    }
    
    public function handleReaction($commentId, $userId) {
        return $this->gererReaction($commentId, $userId);
    }
    
    public function getCommentsByPostId($postId) {
        return $this->recupererCommentsByPostId($postId);
    }
}