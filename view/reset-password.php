<?php
session_start();
require_once('../controller/userC.php');

$message = '';
$messageType = '';
$canReset = false;

// Vérifier si l'utilisateur a été vérifié
if (isset($_SESSION['reset_verified']) && $_SESSION['reset_verified'] === true && isset($_SESSION['reset_email'])) {
    $canReset = true;
} else {
    $message = "Veuillez d'abord vérifier votre identité.";
    $messageType = 'error';
    header("refresh:2;url=forgot-password.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $canReset) {
    if (isset($_POST['password']) && isset($_POST['confirm_password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($password) < 8) {
            $message = "Le mot de passe doit contenir au moins 8 caractères.";
            $messageType = 'error';
        } elseif ($password !== $confirm_password) {
            $message = "Les mots de passe ne correspondent pas.";
            $messageType = 'error';
        } else {
            $userC = new UserC();
            if ($userC->updatePassword($_SESSION['reset_email'], $password)) {
                $message = "Votre mot de passe a été réinitialisé avec succès.";
                $messageType = 'success';
                
                // Nettoyer les variables de session
                unset($_SESSION['reset_verified']);
                unset($_SESSION['reset_email']);
                
                // Rediriger vers la page de connexion après 3 secondes
                header("refresh:3;url=signin.php");
            } else {
                $message = "Une erreur est survenue lors de la réinitialisation du mot de passe.";
                $messageType = 'error';
            }
        }
    }
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

        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 1rem;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: #ffffff;
        }

        .password-strength {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            margin-top: 0.5rem;
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }

        .weak { background-color: #ef4444; width: 33.33%; }
        .medium { background-color: #f59e0b; width: 66.66%; }
        .strong { background-color: #10b981; width: 100%; }

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
        <h1>Réinitialisation du mot de passe</h1>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($canReset): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Entrez votre nouveau mot de passe"
                           minlength="8">
                    <div class="password-strength">
                        <div class="password-strength-bar"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirmez votre nouveau mot de passe">
                </div>

                <button type="submit" class="submit-btn">
                    Réinitialiser le mot de passe
                </button>
            </form>
        <?php endif; ?>

        <a href="signin.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour à la connexion
        </a>
    </div>

    <script>
        // Vérification de la force du mot de passe
        const passwordInput = document.getElementById('password');
        const strengthBar = document.querySelector('.password-strength-bar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Vérifier la longueur
            if (password.length >= 8) strength++;
            
            // Vérifier les caractères spéciaux
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
            
            // Vérifier les chiffres
            if (/\d/.test(password)) strength++;
            
            // Vérifier les lettres majuscules et minuscules
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            
            // Mettre à jour la barre de force
            strengthBar.className = 'password-strength-bar';
            if (strength === 0) strengthBar.style.width = '0';
            else if (strength <= 2) strengthBar.classList.add('weak');
            else if (strength === 3) strengthBar.classList.add('medium');
            else strengthBar.classList.add('strong');
        });
    </script>
</body>
</html> 