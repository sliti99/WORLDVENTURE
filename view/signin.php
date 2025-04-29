<?php
session_start();

// Gestion des messages
$message = '';
$messageType = '';

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = urldecode($_GET['error']);
    $messageType = 'error';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once('../controller/userC.php');
    
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $userC = new UserC();
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        // Vérifier les identifiants
        $user = $userC->verifyLogin($email, $password);
        
        if ($user) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirection selon le rôle
            if ($user['role'] === 'admin') {
                header("Location: liste.php");
            } else {
                header("Location: front-office/index11.php");
            }
            exit();
        } else {
            $message = "Email ou mot de passe incorrect.";
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture - Connexion</title>
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
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../view/front-office/background.jpg');
            background-size: cover;
            background-position: center;
            filter: brightness(0.5) blur(2px);
            z-index: -2;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, rgba(10, 10, 30, 0.4), rgba(0, 70, 140, 0.4));
            z-index: -1;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.5rem 2rem;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 100;
        }

        .logo {
            width: 150px;
            height: auto;
            margin-top: 0.5rem;
        }

        .login-container {
            max-width: 450px;
            margin: 120px auto;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(78, 121, 183, 0.9), rgba(30, 78, 142, 0.9));
            border-radius: 2rem;
            backdrop-filter: blur(6px);
            border: 2px solid #003366;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .login-container h2 {
            font-size: 2.2rem;
            color: #ffffff;
            margin-bottom: 1.5rem;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            color: #e6f7ff;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1e90ff;
            background: rgba(255, 255, 255, 0.2);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            border: none;
            border-radius: 1rem;
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #00bfae, #0099cc);
        }

        .links {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 1rem;
        }

        .links a {
            color: #cceeff;
            text-decoration: none;
            margin: 0 1rem;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #ffffff;
            text-decoration: underline;
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
            margin-top: 1rem;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
        }

        .modal-content {
            max-width: 500px;
            margin: 50px auto;
            background: linear-gradient(135deg, rgba(78, 121, 183, 0.95), rgba(30, 78, 142, 0.95));
            border-radius: 2rem;
            padding: 2rem;
            position: relative;
            border: 2px solid #003366;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.4s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            color: #ffffff;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }

        .alert.success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .alert.error {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-message {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .error-message.show {
            display: block;
            opacity: 1;
        }

        .form-group.has-error input {
            border-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.05);
        }

        .password-strength {
            height: 4px;
            background: #e2e8f0;
            margin-top: 0.5rem;
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background-color: #ef4444; width: 33.33%; }
        .strength-medium { background-color: #f59e0b; width: 66.66%; }
        .strength-strong { background-color: #10b981; width: 100%; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .login-container {
                margin: 100px 1rem;
                padding: 1.5rem;
            }

            .login-container h2 {
                font-size: 1.8rem;
            }

            .form-group input {
                padding: 0.8rem;
            }

            .logo {
                width: 120px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 20px;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>

    <header>
        <a href="front-office/index11.html">
            <img src="../view/front-office/logo.png" alt="WorldVenture Logo" class="logo">
        </a>
    </header>

    <div class="login-container">
        <h2>Connexion</h2>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Votre adresse email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
            </div>

            <button type="submit" class="submit-btn">Se connecter</button>

            <div class="links">
                <a href="#">Mot de passe oublié ?</a>
                <a href="#" onclick="openModal(); return false;">Créer un compte</a>
            </div>

            <div style="text-align: center; margin-top: 1rem;">
                <a href="front-office/index11.html" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                </a>
            </div>
        </form>
    </div>

    <!-- Modal de création de compte -->
    <div id="signupModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2>Créer un compte</h2>
            <form method="POST" action="../controller/register.php" id="signupForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="signup-nom">Nom complet</label>
                        <input type="text" id="signup-nom" name="nom" placeholder="Votre nom complet">
                        <div class="error-message" id="nom-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <input type="email" id="signup-email" name="email" placeholder="Votre email">
                        <div class="error-message" id="email-error"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="signup-password">Mot de passe</label>
                        <input type="password" id="signup-password" name="password" placeholder="Votre mot de passe">
                        <div class="password-strength">
                            <div class="password-strength-bar" id="password-strength-bar"></div>
                        </div>
                        <div class="error-message" id="password-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="signup-confirm-password">Confirmer le mot de passe</label>
                        <input type="password" id="signup-confirm-password" name="confirm_password" placeholder="Confirmez le mot de passe">
                        <div class="error-message" id="confirm-password-error"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="signup-tel">Téléphone</label>
                        <input type="tel" id="signup-tel" name="tel" placeholder="Votre numéro de téléphone">
                        <div class="error-message" id="tel-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="signup-ville">Ville</label>
                        <input type="text" id="signup-ville" name="ville" placeholder="Votre ville">
                        <div class="error-message" id="ville-error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="signup-daten">Date de naissance</label>
                    <input type="date" id="signup-daten" name="daten">
                    <div class="error-message" id="daten-error"></div>
                </div>

                <button type="submit" class="submit-btn">Créer mon compte</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('signupModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('signupModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('signupModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Validation du formulaire
        const form = document.getElementById('signupForm');
        const inputs = form.querySelectorAll('input');

        // Fonction pour afficher les erreurs
        function showError(input, message) {
            const formGroup = input.parentElement;
            const errorDiv = formGroup.querySelector('.error-message');
            formGroup.classList.add('has-error');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
        }

        // Fonction pour cacher les erreurs
        function hideError(input) {
            const formGroup = input.parentElement;
            const errorDiv = formGroup.querySelector('.error-message');
            formGroup.classList.remove('has-error');
            errorDiv.classList.remove('show');
        }

        // Validation de l'email
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Validation du mot de passe
        function validatePassword(password) {
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            let strength = 0;
            if (password.length >= minLength) strength++;
            if (hasUpperCase && hasLowerCase) strength++;
            if (hasNumbers) strength++;
            if (hasSpecialChar) strength++;

            const strengthBar = document.getElementById('password-strength-bar');
            strengthBar.className = 'password-strength-bar';

            if (strength === 0) return false;
            else if (strength <= 2) strengthBar.classList.add('strength-weak');
            else if (strength === 3) strengthBar.classList.add('strength-medium');
            else strengthBar.classList.add('strength-strong');

            return strength >= 3;
        }

        // Validation du téléphone
        function validatePhone(phone) {
            return /^[0-9]{8}$/.test(phone);
        }

        // Validation de la date
        function validateDate(date) {
            const selectedDate = new Date(date);
            const today = new Date();
            const minAge = 18;
            const maxAge = 100;

            const age = today.getFullYear() - selectedDate.getFullYear();
            return age >= minAge && age <= maxAge;
        }

        // Événements de validation en temps réel
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                validateField(this);
            });

            input.addEventListener('blur', function() {
                validateField(this);
            });
        });

        function validateField(input) {
            hideError(input);

            switch(input.id) {
                case 'signup-nom':
                    if (input.value.length < 3) {
                        showError(input, 'Le nom doit contenir au moins 3 caractères');
                    }
                    break;

                case 'signup-email':
                    if (!validateEmail(input.value)) {
                        showError(input, 'Adresse email invalide');
                    }
                    break;

                case 'signup-password':
                    if (!validatePassword(input.value)) {
                        showError(input, 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre');
                    }
                    break;

                case 'signup-confirm-password':
                    if (input.value !== document.getElementById('signup-password').value) {
                        showError(input, 'Les mots de passe ne correspondent pas');
                    }
                    break;

                case 'signup-tel':
                    if (!validatePhone(input.value)) {
                        showError(input, 'Le numéro doit contenir exactement 8 chiffres');
                    }
                    break;

                case 'signup-ville':
                    if (input.value.length < 2) {
                        showError(input, 'Veuillez entrer une ville valide');
                    }
                    break;

                case 'signup-daten':
                    if (!validateDate(input.value)) {
                        showError(input, 'Vous devez avoir entre 18 et 100 ans');
                    }
                    break;
            }
        }

        // Validation du formulaire à la soumission
        form.addEventListener('submit', function(e) {
            let hasErrors = false;
            inputs.forEach(input => {
                validateField(input);
                if (input.parentElement.querySelector('.error-message').classList.contains('show')) {
                    hasErrors = true;
                }
            });

            if (hasErrors) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 