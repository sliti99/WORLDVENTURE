<?php
// Désactiver l'affichage des erreurs PHP
error_reporting(0);
ini_set('display_errors', 0);

// S'assurer que la réponse est toujours en JSON
header('Content-Type: application/json');

try {
    // Vérifier si le dossier faces existe, sinon le créer
    $uploadDir = __DIR__ . '/../uploads/faces/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Recevoir les données JSON
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    if (!isset($data['face_data'])) {
        throw new Exception('Aucune image reçue');
    }

    // Extraire les données de l'image base64
    $imageData = $data['face_data'];
    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $imageData = base64_decode($imageData);

    if ($imageData === false) {
        throw new Exception('Données image invalides');
    }

    // Générer un nom de fichier unique
    $fileName = uniqid() . '.jpg';
    $filePath = $uploadDir . $fileName;

    // Sauvegarder l'image
    if (file_put_contents($filePath, $imageData) === false) {
        throw new Exception('Erreur lors de la sauvegarde de l\'image');
    }

    // Mettre à jour la session avec le chemin de l'image
    session_start();
    $_SESSION['face_image'] = $fileName;

    // Renvoyer une réponse de succès
    echo json_encode([
        'success' => true,
        'message' => 'Image sauvegardée avec succès',
        'file_name' => $fileName
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 