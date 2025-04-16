<?php
require_once '../models/model.php';

class BlogController {
    private $model;

    public function __construct() {
        $this->model = new BlogModel();
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        switch ($action) {
            case 'create':
                $this->createPost();
                break;
            case 'delete':
                $this->deletePost();
                break;
            case 'addComment':
                $this->addComment();
                break;
            default:
                return [
                    'posts' => $this->model->getAllPosts(),
                    'comments' => $this->model->getAllComments()
                ];
        }
    }

    public function getPostById($id) {
        return $this->model->getPostById($id);
    }
    
    public function getComments($postId) {
        return $this->model->getCommentsByPostId($postId);
    }

    private function createPost() {
        $title = $this->sanitizeInput($_POST['title'] ?? '');
        $content = $this->sanitizeInput($_POST['content'] ?? '');

        if (!$title || !$content) {
            die('Title and content are required.');
        }

        $this->model->createPost($title, $content);
        header('Location: blog_backend.php');
        exit;
    }

    private function deletePost() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            die('Post ID is required.');
        }

        $this->model->deletePost($id);
        header('Location: blog_backend.php');
        exit;
    }

    private function addComment() {
        $postId = $_POST['post_id'] ?? null;
        $content = $this->sanitizeInput($_POST['comment'] ?? '');

        if (!$postId || !$content) {
            die('Post ID and comment content are required.');
        }

        $this->model->addComment($postId, $content);
        header("Location: post_details.php?id=$postId");
        exit;
    }

    private function sanitizeInput($data) {
        return htmlspecialchars(trim($data));
    }
}
?>