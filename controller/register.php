<?php
require_once('config.php');
require_once('userC.php');
require_once('../model/user.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $daten = trim($_POST['daten'] ?? '');

    // Validation des données
    $errors = [];

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

    if (empty($errors)) {
        try {
            // Vérifier si l'email existe déjà
            $db = config::getConnexion();
            $checkEmail = $db->prepare("SELECT id FROM user WHERE email = ?");
            $checkEmail->execute([$email]);
            
            if ($checkEmail->rowCount() > 0) {
                header("Location: ../view/signin.php?error=" . urlencode("Cette adresse email est déjà utilisée"));
                exit();
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
                header("Location: ../view/signin.php?success=1");
                exit();
            } else {
                header("Location: ../view/signin.php?error=" . urlencode("Erreur lors de l'inscription"));
                exit();
            }

        } catch (Exception $e) {
            header("Location: ../view/signin.php?error=" . urlencode("Une erreur est survenue"));
            exit();
        }
    } else {
        header("Location: ../view/signin.php?error=" . urlencode(implode(", ", $errors)));
        exit();
    }
} else {
    header("Location: ../view/signin.php");
    exit();
}
?> 