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
            'posts' => $this->pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll(),
            'comments' => $this->pdo->query("SELECT * FROM comments ORDER BY created_at DESC")->fetchAll()
        ];
    }

    public function getAllPosts() {
        return $this->pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
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

    public function createPost($title, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO posts (title, content, slug, author_id, status) VALUES (:title, :content, :slug, :author_id, :status)");
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'slug' => strtolower(str_replace(' ', '-', $title)),
            'author_id' => 1,
            'status' => 'published'
        ]);
    }

    public function deletePost($id) {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function addComment($postId, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => 1,
            'content' => $content
        ]);
    }
}
?>