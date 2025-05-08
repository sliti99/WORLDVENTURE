<?php
// Désactiver l'affichage des erreurs PHP
error_reporting(0);
ini_set('display_errors', 0);

// S'assurer que la réponse est toujours en JSON
header('Content-Type: application/json');

require_once('config.php');
require_once('userC.php');
require_once('../model/user.php');

try {
    // Récupération des données du formulaire
    $formData = $_POST;
    
    // Validation des données
    $errors = [];

    $nom = trim($formData['nom'] ?? '');
    $email = trim($formData['email'] ?? '');
    $password = trim($formData['password'] ?? '');
    $tel = trim($formData['tel'] ?? '');
    $ville = trim($formData['ville'] ?? '');
    $daten = trim($formData['daten'] ?? '');

    if (empty($nom) || strlen($nom) < 3) {
        $errors[] = "Le nom doit contenir au moins 3 caractères";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide";
    }

    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }

    if (empty($tel) || !preg_match("/^[0-9]{8}$/", $tel)) {
        $errors[] = "Le numéro de téléphone doit contenir exactement 8 chiffres";
    }

    if (empty($ville) || strlen($ville) < 2) {
        $errors[] = "La ville est requise";
    }

    if (empty($daten)) {
        $errors[] = "La date de naissance est requise";
    } else {
        $birthDate = new DateTime($daten);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        if ($age < 18 || $age > 100) {
            $errors[] = "Vous devez avoir entre 18 et 100 ans";
        }
    }

    if (!empty($errors)) {
        throw new Exception(implode(", ", $errors));
    }

    // Vérifier si l'email existe déjà
    $db = config::getConnexion();
    $checkEmail = $db->prepare("SELECT id FROM user WHERE email = ?");
    $checkEmail->execute([$email]);
    
    if ($checkEmail->rowCount() > 0) {
        throw new Exception("Cette adresse email est déjà utilisée");
    }

    // Créer un nouvel utilisateur
    $user = new User();
    $user->setNom($nom);
    $user->setEmail($email);
    $user->setMdp(password_hash($password, PASSWORD_BCRYPT));
    $user->setTel($tel);
    $user->setVille($ville);
    $user->setDaten($daten);
    $user->setRole('user'); // Rôle par défaut

    $userC = new UserC();
    $result = $userC->addUser($user);

    if (strpos($result, "succès") !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Inscription réussie'
        ]);
    } else {
        throw new Exception("Erreur lors de l'inscription");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 