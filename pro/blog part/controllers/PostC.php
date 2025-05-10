<?php
require_once '../config/config.php';
require_once '../models/PostModel.php';

class PostC {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Get all posts
    public function afficherPosts() {
        $stmt = $this->pdo->query("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC");
        $postsData = $stmt->fetchAll();
        
        $posts = [];
        foreach ($postsData as $postData) {
            $post = new Post();
            $post->setId($postData['id'] ?? 0);
            $post->setTitle($postData['title'] ?? '');
            $post->setContent($postData['content'] ?? '');
            $post->setAuthorId($postData['author_id'] ?? 0);
            
            // Handle potentially missing fields with null coalescing operator
            $post->setPhotoPath($postData['photo_path'] ?? null);
            $post->setLatitude($postData['latitude'] ?? null);
            $post->setLongitude($postData['longitude'] ?? null);
            $post->setCreatedAt($postData['created_at'] ?? date('Y-m-d H:i:s'));
            
            // Get reaction count for this post
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :postId AND type = 'post'");
            $stmt->execute(['postId' => $post->getId()]);
            $post->setReactions($stmt->fetchColumn());
            
            $posts[] = $post;
        }
        
        return $posts;
    }

    // Add a new post
    public function ajouter($title, $content, $authorId, $photoPath = null, $latitude = null, $longitude = null) {
        try {
            // Generate a unique slug from the title
            $baseSlug = $this->createSlug($title);
            $slug = $baseSlug;
            
            // If slug is empty (could happen with non-latin characters)
            if (empty($slug)) {
                $slug = 'post-' . time();
            }
            
            // Make sure the slug is unique
            $counter = 1;
            while ($this->slugExists($slug)) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
                
                // If the slug is still empty, generate a timestamp-based one
                if (empty($baseSlug)) {
                    $slug = 'post-' . time() . '-' . $counter;
                }
            }
            
            // Get database table columns to see what's actually available
            $columns = $this->getTableColumns('posts');
            $params = [
                'title' => $title,
                'content' => $content,
                'authorId' => $authorId
            ];
            
            $sql = "INSERT INTO posts (title, content, author_id";
            $valuesSql = "VALUES (:title, :content, :authorId";
            
            // Add slug only if column exists
            if (in_array('slug', $columns)) {
                $sql .= ", slug";
                $valuesSql .= ", :slug";
                $params['slug'] = $slug;
            }
            
            // Add photo_path only if column exists
            if (in_array('photo_path', $columns) && $photoPath) {
                $sql .= ", photo_path";
                $valuesSql .= ", :photoPath";
                $params['photoPath'] = $photoPath;
            }
            
            // Add latitude only if column exists and value provided
            if (in_array('latitude', $columns) && $latitude) {
                $sql .= ", latitude";
                $valuesSql .= ", :latitude";
                $params['latitude'] = $latitude;
            }
            
            // Add longitude only if column exists and value provided
            if (in_array('longitude', $columns) && $longitude) {
                $sql .= ", longitude";
                $valuesSql .= ", :longitude";
                $params['longitude'] = $longitude;
            }
            
            // Always add created_at
            $sql .= ", created_at";
            $valuesSql .= ", NOW()";
            
            // Complete the SQL statement
            $sql .= ") " . $valuesSql . ")";
            
            // Log the final SQL statement for debugging
            error_log("Generated SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            
            // Prepare and execute
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute($params);
            
            if (!$success) {
                error_log("SQL error: " . json_encode($stmt->errorInfo()));
                return false;
            }
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in PostC::ajouter: " . $e->getMessage());
            return false;
        }
    }
    
    // Get columns of a database table
    private function getTableColumns($table) {
        try {
            $stmt = $this->pdo->prepare("DESCRIBE " . $table);
            $stmt->execute();
            $columns = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $columns[] = $row['Field'];
            }
            
            return $columns;
        } catch (PDOException $e) {
            error_log("Error getting table columns: " . $e->getMessage());
            // Return minimum required columns to prevent further errors
            return ['id', 'title', 'content', 'author_id', 'created_at'];
        }
    }

    // Helper function to create a slug from title
    private function createSlug($string) {
        if (empty($string)) {
            return 'post-' . time();
        }
        
        // Transliterate accented characters
        $string = $this->transliterateString($string);
        
        // Replace non letter or digits by -
        $string = preg_replace('~[^\pL\d]+~u', '-', $string);
        
        // Trim
        $string = trim($string, '-');
        
        // Lowercase
        $string = strtolower($string);
        
        // Remove unwanted characters
        $string = preg_replace('~[^-a-z0-9]+~', '', $string);
        
        // Ensure we have a non-empty slug
        if (empty($string)) {
            return 'post-' . time();
        }
        
        return $string;
    }
    
    // Safer transliteration function that doesn't depend on iconv
    private function transliterateString($string) {
        // Map of accented and special characters to ASCII
        $chars = array(
            // Latin-1 Supplement
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
            'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o',
            'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE',
            'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I',
            'Î' => 'I', 'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y',
            // Symbols
            '©' => 'c',
            // Other common characters
            'ß' => 'ss', 'þ' => 'th', 'ñ' => 'n'
        );
        
        // Replace accented chars
        $string = strtr($string, $chars);
        
        // Remove any remaining non-ASCII characters
        $string = preg_replace('/[^\x20-\x7E]/u', '', $string);
        
        return $string;
    }

    // Helper function to check if a slug already exists
    private function slugExists($slug) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Get a specific post by ID
    public function detail($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.id = :id");
        $stmt->execute(['id' => $id]);
        $postData = $stmt->fetch();
        
        if (!$postData) {
            return null;
        }
        
        // Handle potentially missing fields with null coalescing operator
        $postData['photo_path'] = $postData['photo_path'] ?? null;
        $postData['latitude'] = $postData['latitude'] ?? null;
        $postData['longitude'] = $postData['longitude'] ?? null;
        
        // Get reaction count
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :postId AND type = 'post'");
        $stmt->execute(['postId' => $id]);
        $postData['reactions'] = (int)$stmt->fetchColumn();
        
        return $postData;
    }

    // Update a post
    public function modifier($id, $title, $content, $photoPath = null, $latitude = null, $longitude = null) {
        $stmt = $this->pdo->prepare("UPDATE posts SET title = :title, content = :content, 
                                     photo_path = :photoPath, latitude = :latitude, longitude = :longitude 
                                     WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'photoPath' => $photoPath,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);
        
        return $stmt->rowCount() > 0;
    }

    // Delete a post
    public function supprimer($id) {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }
    
    // Get latest posts with limit
    public function recupererPosts($limit = 3) {
        $stmt = $this->pdo->prepare("SELECT p.*, u.name AS author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC LIMIT :limit");
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $postsData = $stmt->fetchAll();
        
        // Handle potentially missing fields and get reaction counts
        foreach ($postsData as &$post) {
            // Handle missing fields
            $post['photo_path'] = $post['photo_path'] ?? null;
            $post['latitude'] = $post['latitude'] ?? null;
            $post['longitude'] = $post['longitude'] ?? null;
            
            // Get reaction count
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id = :postId AND type = 'post'");
            $stmt->execute(['postId' => $post['id']]);
            $post['reactions'] = (int)$stmt->fetchColumn();
        }
        
        return $postsData;
    }
    
    // Track user reaction to a post
    public function ajouterReaction($postId, $userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='post'");
        $stmt->execute(['userId' => $userId, 'itemId' => $postId]);
        
        if ((int)$stmt->fetchColumn() > 0) {
            $stmt = $this->pdo->prepare("DELETE FROM reactions_log WHERE user_id=:userId AND item_id=:itemId AND type='post'");
            $stmt->execute(['userId' => $userId, 'itemId' => $postId]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO reactions_log (user_id, item_id, type, created_at) VALUES (:userId, :itemId, 'post', NOW())");
            $stmt->execute(['userId' => $userId, 'itemId' => $postId]);
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE item_id=:itemId AND type='post'");
        $stmt->execute(['itemId' => $postId]);
        $count = (int)$stmt->fetchColumn();
        
        return $count;
    }
    
    // Check if user has reacted to a post
    public function verifierReaction($itemId, $userId) {
        if (!$userId || $userId === 0) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM reactions_log WHERE user_id = :userId AND item_id = :itemId AND type = 'post'");
        $stmt->execute([
            'userId' => $userId,
            'itemId' => $itemId
        ]);
        
        return (int)$stmt->fetchColumn() > 0;
    }
    
    // Alias for backward compatibility - will be deprecated
    public function hasUserReacted($itemId, $userId) {
        return $this->verifierReaction($itemId, $userId);
    }
    
    // Handle post reaction (standardized name)
    public function gererReaction($postId, $userId) {
        return $this->ajouterReaction($postId, $userId);
    }
    
    // Alias for backward compatibility - will be deprecated
    public function handleReaction($postId, $userId) {
        return $this->gererReaction($postId, $userId);
    }
}