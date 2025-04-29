<?php
require_once '../controller/config.php';

$message = '';
$messageType = '';

try {
    $email = 'aze@gmail.com';
    $new_password = 'Avanti.12';
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    $db = config::getConnexion();
    $query = $db->prepare("UPDATE user SET mdp = :mdp WHERE email = :email");
    $result = $query->execute([
        'email' => $email,
        'mdp' => $hashed_password
    ]);
    
    if ($result) {
        // Vérification
        $verify_query = $db->prepare("SELECT mdp FROM user WHERE email = :email");
        $verify_query->execute(['email' => $email]);
        $user = $verify_query->fetch();
        
        if ($user && password_verify($new_password, $user['mdp'])) {
            $message = "Mot de passe mis à jour avec succès";
            $messageType = "success";
        } else {
            $message = "Échec de la vérification du nouveau mot de passe";
            $messageType = "error";
        }
    } else {
        $message = "Échec de la mise à jour du mot de passe";
        $messageType = "error";
    }
    
} catch (Exception $e) {
    $message = "Erreur: " . $e->getMessage();
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture - Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e4e8e, #0a2744);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: #ffffff;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #ffffff;
            margin-bottom: 2rem;
            font-size: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .message.success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .message.error {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .back-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        .success-icon {
            color: #10b981;
        }

        .error-icon {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Réinitialisation du mot de passe</h1>
        
        <?php if ($message): ?>
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle success-icon' : 'exclamation-circle error-icon'; ?> icon"></i>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <a href="signin.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour à la connexion
        </a>
    </div>
</body>
</html> 