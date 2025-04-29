<?php
session_start();
require_once('../../controller/userC.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin.php");
    exit();
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $userC = new UserC();
    
    try {
        $user = $userC->showUser($_SESSION['user_id']);
        if (!$user) {
            $error = "Compte introuvable. Il a peut-être déjà été supprimé.";
        } else {
            if ($userC->deleteUser($_SESSION['user_id'])) {
                session_destroy();
                header("Location: ../signin.php?message=" . urlencode("Votre compte a été supprimé avec succès."));
                exit();
            } else {
                $error = "Une erreur est survenue lors de la suppression du compte. Veuillez réessayer plus tard.";
            }
        }
    } catch (Exception $e) {
        $error = "Une erreur inattendue est survenue. Veuillez réessayer plus tard.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture - Supprimer mon compte</title>
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
            max-width: 600px;
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

        .delete-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .warning-icon {
            font-size: 4rem;
            color: #ef4444;
            margin-bottom: 1.5rem;
        }

        .delete-title {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #ef4444;
        }

        .delete-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #cceeff;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 1rem 2rem;
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

        .delete-btn {
            background: #ef4444;
            color: white;
        }

        .cancel-btn {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .btn:hover {
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

            .delete-card {
                padding: 1.5rem;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
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

        <div class="delete-card">
            <i class="fas fa-exclamation-triangle warning-icon"></i>
            <h1 class="delete-title">Supprimer mon compte</h1>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <p class="delete-message">
                Attention ! Cette action est irréversible.<br>
                Toutes vos données seront définitivement supprimées.<br>
                Êtes-vous sûr de vouloir continuer ?
            </p>

            <form method="POST" action="" class="actions">
                <button type="submit" name="confirm_delete" class="btn delete-btn">
                    <i class="fas fa-trash-alt"></i>
                    Oui, supprimer mon compte
                </button>
                <a href="profile.php" class="btn cancel-btn">
                    <i class="fas fa-times"></i>
                    Non, annuler
                </a>
            </form>
        </div>
    </div>
</body>
</html> 