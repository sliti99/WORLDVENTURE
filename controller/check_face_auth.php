<?php
// Désactiver l'affichage des erreurs PHP
error_reporting(0);
ini_set('display_errors', 0);

// S'assurer que la réponse est toujours en JSON
header('Content-Type: application/json');

try {
    session_start();
    require_once('userC.php');

    // Vérifier si la requête est en JSON
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if ($contentType !== "application/json") {
        throw new Exception('Le contenu doit être en JSON');
    }

    // Recevoir les données JSON
    $jsonData = file_get_contents('php://input');
    if (!$jsonData) {
        throw new Exception('Aucune donnée reçue');
    }

    $data = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }

    $email = $data['email'] ?? '';
    if (empty($email)) {
        throw new Exception('Email non fourni');
    }

    // Vérifier si l'email existe
    $userC = new UserC();
    $user = $userC->getUserByEmail($email);

    if ($user) {
        // Simuler une authentification réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        echo json_encode([
            'success' => true,
            'role' => $user['role']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non trouvé'
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 