<?php
// Model class following strict anemic pattern - only properties and getters/setters
class BlogModel {
    private $posts = [];
    private $comments = [];
    
    // Get all posts
    public function getPosts(): array {
        return $this->posts;
    }

    // Get all comments
    public function getComments(): array {
        return $this->comments;
    }
    
    // Set posts array
    public function setPosts(array $posts): void {
        $this->posts = $posts;
    }
    
    // Set comments array
    public function setComments(array $comments): void {
        $this->comments = $comments;
    }
}