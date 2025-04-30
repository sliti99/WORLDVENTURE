<?php
class BlogModel {
    private $posts = [];
    private $comments = [];

    // Add a post to the collection
    public function addPost(Post $post): void {
        $this->posts[] = $post;
    }

    // Add a comment to the collection
    public function addComment(Comment $comment): void {
        $this->comments[] = $comment;
    }
    
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
    
    // Get a post by ID
    public function getPostById(int $id): ?Post {
        foreach ($this->posts as $post) {
            if ($post->getId() === $id) {
                return $post;
            }
        }
        return null;
    }
    
    // Get comments for a specific post
    public function getCommentsByPostId(int $postId): array {
        $postComments = [];
        foreach ($this->comments as $comment) {
            if ($comment->getPostId() === $postId) {
                $postComments[] = $comment;
            }
        }
        return $postComments;
    }
}