<?php
session_start();
require_once('../controller/userC.php');

header('Content-Type: application/json');

// Simuler une vérification d'authentification par Face ID
// Dans un environnement de production, vous devriez implémenter une vraie vérification biométrique
try {
    // Récupérer les données envoyées
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data['faceDetected']) {
        $userC = new UserC();
        
        // Pour la démonstration, on simule une connexion réussie
        $_SESSION['user_id'] = 1; // ID par défaut pour la démo
        $_SESSION['user_role'] = 'user'; // Rôle par défaut
        
        // Retourner la réponse
        echo json_encode([
            'success' => true,
            'role' => $_SESSION['user_role'],
            'message' => 'Authentification réussie'
        ]);
    } else {
        throw new Exception('Aucun visage détecté');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 