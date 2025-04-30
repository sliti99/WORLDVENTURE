<?php
class Post {
    private $id;
    private $title;
    private $content;
    private $author_id;
    private $photo_path;
    private $latitude;
    private $longitude;
    private $created_at;
    private $reactions;

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function getContent(): ?string { return $this->content; }
    public function getAuthorId(): ?int { return $this->author_id; }
    public function getPhotoPath(): ?string { return $this->photo_path; }
    public function getLatitude(): ?float { return $this->latitude; }
    public function getLongitude(): ?float { return $this->longitude; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getReactions(): ?int { return $this->reactions; }

    // Setters
    public function setId(int $id): void { $this->id = $id; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function setContent(string $content): void { $this->content = $content; }
    public function setAuthorId(int $author_id): void { $this->author_id = $author_id; } 
    public function setPhotoPath(?string $photo_path): void { $this->photo_path = $photo_path; }
    public function setLatitude(?float $latitude): void { $this->latitude = $latitude; }
    public function setLongitude(?float $longitude): void { $this->longitude = $longitude; }
    public function setCreatedAt(string $created_at): void { $this->created_at = $created_at; }
    public function setReactions(int $reactions): void { $this->reactions = $reactions; }
}