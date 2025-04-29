<?php
session_start();
require_once('../../controller/userC.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../signin.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$userC = new UserC();
$user = $userC->showUser($_SESSION['user_id']);

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $tel = $_POST['tel'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $daten = $_POST['daten'] ?? '';
    
    // Mise à jour du mot de passe uniquement s'il est fourni
    $password = !empty($_POST['password']) ? $_POST['password'] : $user['mdp'];
    
    // TODO: Ajouter la méthode updateUser dans la classe UserC
    if ($userC->updateUser($_SESSION['user_id'], $nom, $email, $password, $tel, $ville, $daten)) {
        header("Location: profile.php?success=1");
        exit();
    } else {
        $error = "Une erreur est survenue lors de la mise à jour du profil.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture - Modifier mon profil</title>
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
            color: #ffffff;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .logo {
            width: 150px;
            height: auto;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.8rem 1.5rem;
            border-radius: 2rem;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .edit-form {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .form-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #ffffff;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #cceeff;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1e90ff;
            background: rgba(255, 255, 255, 0.2);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .password-note {
            font-size: 0.8rem;
            color: #cceeff;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .submit-btn {
            padding: 1rem 3rem;
            border-radius: 2rem;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .save-btn {
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            color: white;
        }

        .cancel-btn {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .edit-form {
                padding: 1.5rem;
            }

            .actions {
                flex-direction: column;
            }

            .submit-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="profile.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Retour au profil
            </a>
            <img src="logo.png" alt="WorldVenture Logo" class="logo">
        </header>

        <form class="edit-form" method="POST" action="">
            <h1 class="form-title">Modifier mon profil</h1>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-column">
                    <div class="form-group">
                        <label for="nom">Nom complet</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="Laissez vide pour ne pas modifier">
                        <p class="password-note">Minimum 8 caractères, une majuscule, une minuscule et un chiffre</p>
                    </div>
                </div>
                <div class="form-column">
                    <div class="form-group">
                        <label for="tel">Téléphone</label>
                        <input type="tel" id="tel" name="tel" value="<?php echo htmlspecialchars($user['tel']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($user['ville']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="daten">Date de naissance</label>
                        <input type="date" id="daten" name="daten" value="<?php echo htmlspecialchars($user['daten']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="submit-btn save-btn">
                    <i class="fas fa-save"></i>
                    Enregistrer les modifications
                </button>
                <a href="profile.php" class="submit-btn cancel-btn">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
            </div>
        </form>
    </div>

    <script>
        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            
            if (password !== '' && !validatePassword(password)) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre');
            }
        });

        function validatePassword(password) {
            if (password === '') return true; // Pas de validation si le champ est vide
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers;
        }
    </script>
</body>
</html> 