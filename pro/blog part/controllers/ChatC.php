<?php
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../config/FilterService.php';

class ChatC {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Add a new chat message
     * @param int $userId User ID of the sender
     * @param string $content Message content
     * @param string $userName Name of the user
     * @param string $userRole Role of the user (admin, user, etc.)
     * @return int|bool The ID of the new message or false on failure
     */
    public function ajouterMessage($userId, $content, $userName, $userRole) {
        // Filter content using FilterService
        $filterResult = FilterService::filterContent($content);
        if (!$filterResult['clean']) {
            error_log("Chat message rejected due to inappropriate content");
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO chat_messages (user_id, content, user_name, user_role, created_at) 
                VALUES (:userId, :content, :userName, :userRole, NOW())
            ");
            
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->bindParam(':userName', $userName, PDO::PARAM_STR);
            $stmt->bindParam(':userRole', $userRole, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log("Error adding chat message: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve a specific number of recent chat messages
     * @param int $limit Maximum number of messages to retrieve
     * @return array Array of chat messages
     */
    public function recupererMessages($limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, content, user_name, user_role, created_at
                FROM chat_messages
                ORDER BY created_at DESC
                LIMIT :limit
            ");
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return messages in chronological order (oldest first)
            return array_reverse($messages);
        } catch (Exception $e) {
            error_log("Error retrieving chat messages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Retrieve a specific message by ID
     * @param int $messageId ID of the message to retrieve
     * @return array|null Message data or null if not found
     */
    public function recupererMessageById($messageId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, content, user_name, user_role, created_at
                FROM chat_messages
                WHERE id = :messageId
            ");
            
            $stmt->bindParam(':messageId', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error retrieving chat message by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Delete a message by ID (admin function)
     * @param int $messageId ID of the message to delete
     * @return bool True on success, false on failure
     */
    public function supprimerMessage($messageId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM chat_messages WHERE id = :messageId");
            $stmt->bindParam(':messageId', $messageId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting chat message: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get chat messages for a specific user
     * @param int $userId User ID to filter messages for
     * @param int $limit Maximum number of messages to retrieve
     * @return array Array of chat messages
     */
    public function recupererMessagesParUser($userId, $limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, content, user_name, user_role, created_at
                FROM chat_messages
                WHERE user_id = :userId
                ORDER BY created_at DESC
                LIMIT :limit
            ");
            
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return messages in chronological order (oldest first)
            return array_reverse($messages);
        } catch (Exception $e) {
            error_log("Error retrieving chat messages for user: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verify user session status for chat participation
     * @param int $userId User ID to verify
     * @return bool True if user is valid, false otherwise
     */
    public function verifierUtilisateur($userId) {
        if (!$userId || $userId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM users WHERE id = :userId
            ");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error verifying user for chat: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the number of new chat messages since a given timestamp
     * @param string $timestamp Timestamp to check from
     * @return int Number of new messages
     */
    public function compterNouveauxMessages($timestamp) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM chat_messages
                WHERE created_at > :timestamp
            ");
            
            $stmt->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting new chat messages: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * For backward compatibility
     * @param int $userId User ID to verify
     * @return bool True if user is valid, false otherwise
     */
    public function checkUser($userId) {
        return $this->verifierUtilisateur($userId);
    }
    
    /**
     * For backward compatibility
     * @param string $timestamp Timestamp to check from
     * @return int Number of new messages
     */
    public function countNewMessages($timestamp) {
        return $this->compterNouveauxMessages($timestamp);
    }
}
?>