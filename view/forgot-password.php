<?php
session_start();
require_once('../controller/userC.php');

$message = '';
$messageType = '';
$showResetForm = false;
$userFound = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userC = new UserC();
    
    if (isset($_POST['email']) && !isset($_POST['daten'])) {
        // Première étape : vérification de l'email
        $email = trim($_POST['email']);
        $user = $userC->getUserByEmail($email);
        
        if ($user) {
            $_SESSION['reset_email'] = $email;
            $showResetForm = true;
        } else {
            $message = "Aucun compte trouvé avec cette adresse email.";
            $messageType = 'error';
        }
    } 
    elseif (isset($_POST['daten']) && isset($_POST['ville'])) {
        // Deuxième étape : vérification des informations personnelles
        $email = $_SESSION['reset_email'];
        $daten = $_POST['daten'];
        $ville = trim($_POST['ville']);
        
        $user = $userC->getUserByEmail($email);
        
        if ($user && $user['daten'] == $daten && strtolower($user['ville']) == strtolower($ville)) {
            // Informations correctes, afficher le formulaire de nouveau mot de passe
            $_SESSION['reset_verified'] = true;
            header("Location: reset-password.php");
            exit();
        } else {
            $message = "Les informations fournies ne correspondent pas à nos enregistrements.";
            $messageType = 'error';
            $showResetForm = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture - Mot de passe oublié</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e4e8e, #4e79b7);
        }

        .container {
            max-width: 450px;
            margin: 100px auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #ffffff;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        input[type="email"],
        input[type="date"],
        input[type="text"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 1rem;
        }

        input[type="date"] {
            color-scheme: dark;
        }

        input:focus {
            outline: none;
            border-color: #ffffff;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .alert.success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }

        .alert.error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 1rem;
            font-size: 0.9rem;
        }

        .step.active {
            background: #1e90ff;
        }

        @media (max-width: 480px) {
            .container {
                margin: 50px 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mot de passe oublié</h1>
        
        <div class="step-indicator">
            <div class="step <?php echo !$showResetForm ? 'active' : ''; ?>">1</div>
            <div class="step <?php echo $showResetForm ? 'active' : ''; ?>">2</div>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!$showResetForm): ?>
        <!-- Étape 1 : Email -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="Entrez votre adresse email">
            </div>

            <button type="submit" class="submit-btn">
                Continuer
            </button>
        </form>

        <?php else: ?>
        <!-- Étape 2 : Vérification -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="daten">Date de naissance</label>
                <input type="date" id="daten" name="daten" required>
            </div>

            <div class="form-group">
                <label for="ville">Ville</label>
                <input type="text" id="ville" name="ville" required 
                       placeholder="Entrez votre ville">
            </div>

            <button type="submit" class="submit-btn">
                Vérifier l'identité
            </button>
        </form>
        <?php endif; ?>

        <a href="signin.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour à la connexion
        </a>
    </div>
</body>
</html> 