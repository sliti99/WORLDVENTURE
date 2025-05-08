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
        // Vérification du reCAPTCHA
        $recaptcha_secret = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe";
        $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        
        if (empty($recaptcha_response)) {
            $message = "Veuillez compléter le reCAPTCHA.";
            $messageType = 'error';
        } else {
            // Vérification du reCAPTCHA avec l'API Google
            $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_secret.'&response='.$recaptcha_response);
            $response_data = json_decode($verify_response);
            
            if (!$response_data->success) {
                $message = "La vérification reCAPTCHA a échoué. Veuillez réessayer.";
                if (isset($response_data->{'error-codes'}) && is_array($response_data->{'error-codes'})) {
                    // Log détaillé des erreurs pour le débogage
                    error_log("reCAPTCHA error: " . implode(", ", $response_data->{'error-codes'}));
                }
                $messageType = 'error';
            } else {
                // Score minimum requis (0.5 est la valeur recommandée)
                $score_threshold = 0.5;
                
                // Vérification des identifiants
                $userC = new UserC();
                $email = trim($_POST['email']);
                $password = trim($_POST['password']);
                
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
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            background: linear-gradient(135deg, #1e4e8e, #4e79b7);
            z-index: -2;
        }

        /* Fallback si l'image ne charge pas */
        .background-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1e4e8e, #4e79b7);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .background-image.error::after {
            opacity: 1;
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

        /* Styles pour le reCAPTCHA */
        .g-recaptcha {
            transform-origin: left top;
            margin: 10px 0;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
        }

        .g-recaptcha iframe {
            border-radius: 4px;
        }

        .recaptcha-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px 0;
        }

        .recaptcha-error {
            color: #ef4444;
            font-size: 0.9rem;
            margin-top: 8px;
            text-align: center;
            background: rgba(239, 68, 68, 0.1);
            padding: 8px 12px;
            border-radius: 4px;
            display: none;
        }

        .recaptcha-error.show {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @media screen and (max-width: 480px) {
            .g-recaptcha {
                transform: scale(0.85);
                margin: 10px -20px;
            }
            
            .recaptcha-container {
                margin: 10px 0;
            }
        }

        /* Style pour le bouton désactivé */
        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            background: linear-gradient(135deg, #808080, #606060);
        }

        /* Animation de chargement pour le reCAPTCHA */
        .recaptcha-loading {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 10px auto;
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        .face-id-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        #status-message {
            transition: all 0.3s ease;
        }

        #status-message.success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        #status-message.error {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        #status-message.processing {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        @keyframes scanning {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        .scanning-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #1e90ff, transparent);
            animation: scanning 2s linear infinite;
        }

        #face-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 2px solid #1e90ff;
            border-radius: 50%;
            pointer-events: none;
        }

        .detection-box {
            position: absolute;
            border: 2px solid #1e90ff;
            border-radius: 4px;
            background-color: rgba(30, 144, 255, 0.2);
        }

        .loading-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 1rem 2rem;
            border-radius: 1rem;
            text-align: center;
        }

        .scanning-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, 
                transparent 0%,
                rgba(30, 144, 255, 0.2) 50%,
                transparent 100%
            );
            animation: scan 2s linear infinite;
            pointer-events: none;
        }

        @keyframes scan {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        #camera-container {
            width: 100%;
            max-width: 400px;
            height: 300px;
            margin: 20px auto;
            position: relative;
            border-radius: 1rem;
            overflow: hidden;
            background: #000;
        }

        .face-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 3px solid #1e90ff;
            border-radius: 50%;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
        }

        .face-guide::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 210px;
            height: 210px;
            border: 2px solid rgba(30, 144, 255, 0.3);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(0.95); opacity: 0.5; }
            50% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(0.95); opacity: 0.5; }
        }

        .simulated-video {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1a1a1a, #333);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.1);
            font-size: 48px;
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

        <form method="POST" action="" id="loginForm" onsubmit="validateForm(event)">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Votre adresse email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
            </div>

            <div class="form-group" style="display: flex; justify-content: center; margin: 20px 0;">
                <div class="g-recaptcha" 
                     data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"
                     data-callback="enableSubmit"
                     data-expired-callback="disableSubmit"
                     data-error-callback="disableSubmit"></div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">Se connecter</button>

            <div class="face-id-section" style="text-align: center; margin-top: 1rem;">
                <button type="button" class="face-id-btn" onclick="startFaceID()" style="
                    background: none;
                    border: 2px solid rgba(255, 255, 255, 0.2);
                    border-radius: 1rem;
                    padding: 0.8rem 1.5rem;
                    color: white;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto;
                    transition: all 0.3s ease;
                ">
                    <i class="fas fa-face-viewfinder" style="margin-right: 8px;"></i>
                    Se connecter avec Face ID
                </button>
            </div>

            <div class="links">
                <a href="forgot-password.php">Mot de passe oublié ?</a>
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
            <div id="signup-step-1">
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

                    <input type="hidden" name="face_data" id="face_data">
                    <button type="submit" class="submit-btn">Créer mon compte</button>
                </form>
            </div>

            <!-- Face Capture Step -->
            <div id="signup-step-2" style="display: none;">
                <h2>Configurer Face ID</h2>
                <div id="face-capture-container" style="
                    width: 100%;
                    max-width: 400px;
                    height: 300px;
                    margin: 20px auto;
                    position: relative;
                    border-radius: 1rem;
                    overflow: hidden;
                ">
                    <video id="signup-video" style="
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        border-radius: 1rem;
                    " autoplay playsinline></video>
                    <canvas id="signup-canvas" style="display: none;"></canvas>
                    <div class="face-guide"></div>
                    <div class="scanning-animation"></div>
                </div>
                <div id="capture-status" style="
                    margin: 1rem 0;
                    text-align: center;
                    font-weight: 500;
                    color: #ffffff;
                "></div>
                <button onclick="captureFace()" class="submit-btn" style="margin-top: 1rem;">
                    Capturer mon visage
                </button>
            </div>

            <!-- Email Verification Step -->
            <div id="signup-step-3" style="display: none;">
                <h2>Vérification Email</h2>
                <p style="text-align: center; margin: 2rem 0;">
                    Un email de vérification a été envoyé à votre adresse email.
                    Veuillez vérifier votre boîte de réception et cliquer sur le lien de confirmation.
                </p>
                <div style="text-align: center;">
                    <button onclick="checkEmailVerification()" class="submit-btn">
                        J'ai vérifié mon email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Face ID -->
    <div id="faceIDModal" class="modal">
        <div class="modal-content" style="text-align: center;">
            <span class="close-modal" onclick="closeFaceIDModal()">&times;</span>
            <h2>Connexion Face ID</h2>
            <div id="camera-container">
                <video id="video" style="
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    border-radius: 1rem;
                " autoplay playsinline></video>
                <div class="face-guide"></div>
                <div class="scanning-animation"></div>
            </div>
            <div id="status-message" style="margin-top: 1rem;"></div>
        </div>
    </div>

    <script>
        // Ajouter la gestion d'erreur pour l'image de fond
        window.addEventListener('load', function() {
            const bgImage = document.querySelector('.background-image');
            const img = new Image();
            img.src = '../view/front-office/background.jpg';
            
            img.onerror = function() {
                bgImage.classList.add('error');
            };
        });

        function openModal() {
            document.getElementById('signupModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('signupModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.onclick = function(event) {
            const signupModal = document.getElementById('signupModal');
            const faceIDModal = document.getElementById('faceIDModal');
            if (event.target == signupModal) {
                closeModal();
            } else if (event.target == faceIDModal) {
                closeFaceIDModal();
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

        // Face ID Implementation with face-api.js
        let videoStream = null;
        let isModelLoaded = false;

        async function loadFaceApiModels() {
            const modelPath = 'https://justadudewhohacks.github.io/face-api.js/models';
            try {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(modelPath),
                    faceapi.nets.faceLandmark68Net.loadFromUri(modelPath),
                    faceapi.nets.faceRecognitionNet.loadFromUri(modelPath)
                ]);
                isModelLoaded = true;
                return true;
            } catch (error) {
                console.error('Erreur de chargement des modèles:', error);
                return false;
            }
        }

        async function startFaceID() {
            const emailInput = document.getElementById('email');
            
            // Vérifier si l'email est saisi
            if (!emailInput.value) {
                alert('Veuillez saisir votre email avant d\'utiliser Face ID');
                return;
            }

            const modal = document.getElementById('faceIDModal');
            const video = document.getElementById('video');
            const statusMessage = document.getElementById('status-message');
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';

            try {
                // Demander l'accès à la caméra
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    } 
                });
                
                video.srcObject = stream;
                videoStream = stream;
                
                // Démarrer la simulation d'authentification
                startFaceDetection();
                
            } catch (error) {
                console.error('Erreur d\'accès à la caméra:', error);
                statusMessage.textContent = "Erreur d'accès à la caméra. Veuillez vérifier vos permissions.";
                statusMessage.className = 'error';
            }
        }

        function closeFaceIDModal() {
            const modal = document.getElementById('faceIDModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Arrêter la caméra
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        async function startFaceDetection() {
            const statusMessage = document.getElementById('status-message');
            const emailInput = document.getElementById('email');

            statusMessage.textContent = "Initialisation de Face ID...";
            statusMessage.className = 'processing';

            // Simuler une détection de 3 secondes
            setTimeout(() => {
                statusMessage.textContent = "Visage détecté ! Authentification...";
                
                // Simuler l'authentification après 2 secondes
                setTimeout(async () => {
                    try {
                        const response = await fetch('../controller/check_face_auth.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                email: emailInput.value,
                                faceDetected: true
                            })
                        });

                        let data;
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            data = await response.json();
                        } else {
                            throw new Error("La réponse du serveur n'est pas au format JSON");
                        }

                        if (data.success) {
                            statusMessage.textContent = "Authentification réussie !";
                            statusMessage.className = 'success';
                            
                            // Redirection basée sur le rôle
                            setTimeout(() => {
                                if (data.role === 'admin') {
                                    window.location.href = 'liste.php';
                                } else {
                                    window.location.href = 'front-office/index11.php';
                                }
                            }, 1000);
                        } else {
                            throw new Error(data.message || 'Authentification échouée');
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        statusMessage.textContent = error.message || "Une erreur est survenue lors de l'authentification";
                        statusMessage.className = 'error';
                        setTimeout(closeFaceIDModal, 2000);
                    }
                }, 2000);
            }, 3000);
        }

        // Variables pour la capture du visage
        let signupVideoStream = null;
        let faceDetected = false;
        let currentStep = 1;

        // Fonction pour passer à l'étape suivante
        function goToStep(step) {
            document.getElementById(`signup-step-${currentStep}`).style.display = 'none';
            document.getElementById(`signup-step-${step}`).style.display = 'block';
            currentStep = step;

            if (step === 2) {
                startFaceCapture();
            }
        }

        // Gestion du formulaire d'inscription
        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Vérification des champs
            let hasErrors = false;
            const inputs = this.querySelectorAll('input');
            inputs.forEach(input => {
                validateField(input);
                if (input.parentElement.querySelector('.error-message').classList.contains('show')) {
                    hasErrors = true;
                }
            });

            if (hasErrors) {
                return;
            }

            try {
                const formData = new FormData(this);
                const response = await fetch('../controller/register.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }

                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("La réponse du serveur n'est pas au format JSON");
                }

                const data = await response.json();
                
                if (data.success) {
                    // Afficher un message de succès
                    const alert = document.createElement('div');
                    alert.className = 'alert success';
                    alert.textContent = data.message;
                    this.insertBefore(alert, this.firstChild);

                    // Passer à l'étape suivante après un court délai
                    setTimeout(() => {
                        goToStep(2);
                    }, 1500);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                
                // Afficher l'erreur dans le formulaire
                const alert = document.createElement('div');
                alert.className = 'alert error';
                alert.textContent = error.message || "Une erreur est survenue lors de l'inscription";
                this.insertBefore(alert, this.firstChild);

                // Supprimer le message d'erreur après 5 secondes
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }
        });

        async function startFaceCapture() {
            const video = document.getElementById('signup-video');
            const statusMessage = document.getElementById('capture-status');
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'user',
                        width: { ideal: 400 },
                        height: { ideal: 300 }
                    } 
                });
                
                signupVideoStream = stream;
                video.srcObject = stream;
                
                // Afficher un message de guidage simple
                statusMessage.textContent = "Positionnez votre visage dans le cercle";
                statusMessage.style.color = '#ffffff';
                
            } catch (error) {
                console.error('Erreur d\'accès à la caméra:', error);
                statusMessage.textContent = "Erreur d'accès à la caméra";
                statusMessage.style.color = '#ef4444';
            }
        }

        async function captureFace() {
            const video = document.getElementById('signup-video');
            const canvas = document.getElementById('signup-canvas');
            const context = canvas.getContext('2d');
            const statusMessage = document.getElementById('capture-status');

            try {
                statusMessage.textContent = "Capture en cours...";
                statusMessage.style.color = '#ffffff';

                // Vérifier que la vidéo est bien chargée
                if (!video.videoWidth || !video.videoHeight) {
                    throw new Error('La caméra n\'est pas encore prête');
                }

                // Capturer l'image
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                let imageData;
                try {
                    imageData = canvas.toDataURL('image/jpeg', 0.8);
                } catch (e) {
                    throw new Error('Erreur lors de la capture de l\'image');
                }

                // Vérifier que l'image n'est pas vide
                if (!imageData || imageData === 'data:,') {
                    throw new Error('Image invalide');
                }

                statusMessage.textContent = "Envoi de l'image...";

                // Envoyer l'image au serveur
                const response = await fetch('../controller/save_face.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        face_data: imageData
                    })
                });

                if (!response.ok) {
                    throw new Error(`Erreur serveur: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    statusMessage.textContent = "Image sauvegardée avec succès !";
                    statusMessage.style.color = '#10b981';

                    // Arrêter la caméra
                    if (signupVideoStream) {
                        signupVideoStream.getTracks().forEach(track => track.stop());
                    }
                    
                    // Attendre un peu pour montrer le message de succès
                    setTimeout(() => {
                        // Passer à l'étape de vérification email
                        goToStep(3);
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Erreur lors de la sauvegarde');
                }
            } catch (error) {
                console.error('Erreur détaillée:', error);
                statusMessage.textContent = error.message || "Une erreur est survenue. Veuillez réessayer.";
                statusMessage.style.color = '#ef4444';
            }
        }

        async function checkEmailVerification() {
            try {
                const response = await fetch('../controller/check_email_verification.php');
                const data = await response.json();
                
                if (data.verified) {
                    alert('Compte vérifié avec succès !');
                    window.location.href = 'signin.php';
                } else {
                    alert('Veuillez vérifier votre email pour continuer');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la vérification');
            }
        }

        // Fonction pour vérifier si le reCAPTCHA est complété
        function validateForm(event) {
            event.preventDefault();
            var response = grecaptcha.getResponse();
            
            if (response.length === 0) {
                alert('Veuillez compléter le reCAPTCHA');
                return false;
            }
            
            // Si le reCAPTCHA est validé, soumettre le formulaire
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html> 