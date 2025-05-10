<?php
class Comment {
    private $id;
    private $postId;
    private $content;
    private $userId;
    private $createdAt;
    private $authorName;
    private $reactions;
    
    // Getters and Setters
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function getPostId() {
        return $this->postId;
    }
    
    public function setPostId($postId) {
        $this->postId = $postId;
        return $this;
    }
    
    public function getContent() {
        return $this->content;
    }
    
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function setUserId($userId) {
        $this->userId = $userId;
        return $this;
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }
    
    public function getAuthorName() {
        return $this->authorName;
    }
    
    public function setAuthorName($authorName) {
        $this->authorName = $authorName;
        return $this;
    }
    
    public function getReactions() {
        return $this->reactions;
    }
    
    public function setReactions($reactions) {
        $this->reactions = $reactions;
        return $this;
    }
}