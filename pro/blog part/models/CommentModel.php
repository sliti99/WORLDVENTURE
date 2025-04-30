<?php
class Comment {
    private $id;
    private $post_id;
    private $content;
    private $user_id;
    private $created_at;
    private $reactions;
    private $author_name;

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getPostId(): ?int { return $this->post_id; }
    public function getContent(): ?string { return $this->content; }
    public function getUserId(): ?int { return $this->user_id; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getReactions(): ?int { return $this->reactions; }
    public function getAuthorName(): ?string { return $this->author_name; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setPostId(int $post_id): void { $this->post_id = $post_id; }
    public function setContent(string $content): void { $this->content = $content; }
    public function setUserId(int $user_id): void { $this->user_id = $user_id; }
    public function setCreatedAt(string $created_at): void { $this->created_at = $created_at; }
    public function setReactions(int $reactions): void { $this->reactions = $reactions; }
    public function setAuthorName(?string $author_name): void { $this->author_name = $author_name; }
}