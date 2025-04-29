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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture - Mon Profil</title>
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

        .profile-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 3rem;
            color: white;
            border: 4px solid rgba(255, 255, 255, 0.2);
        }

        .profile-name {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .profile-email {
            color: #cceeff;
            font-size: 1.1rem;
        }

        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .info-group {
            margin-bottom: 1.5rem;
        }

        .info-label {
            color: #cceeff;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 1.1rem;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 1rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
        }

        .action-btn {
            padding: 1rem 2rem;
            border-radius: 2rem;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            color: white;
        }

        .delete-btn {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .profile-card {
                padding: 1.5rem;
            }

            .actions {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="index11.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Retour à l'accueil
            </a>
            <img src="logo.png" alt="WorldVenture Logo" class="logo">
        </header>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars($user['nom']); ?></h1>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="profile-info">
                <div class="info-column">
                    <div class="info-group">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['tel']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Ville</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['ville']); ?></div>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-group">
                        <div class="info-label">Date de naissance</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['daten']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Rôle</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="edit_profile.php" class="action-btn edit-btn">
                    <i class="fas fa-edit"></i>
                    Modifier mon profil
                </a>
                <a href="#" onclick="confirmDelete()" class="action-btn delete-btn">
                    <i class="fas fa-trash-alt"></i>
                    Supprimer mon compte
                </a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            if (confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
                window.location.href = 'delete_account.php';
            }
        }
    </script>
</body>
</html> 