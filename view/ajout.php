<?php

// Inclure les fichiers nécessaires
require_once('C:\xampp\htdocs\user\controller\userC.php');
require_once('C:\xampp\htdocs\user\model\user.php');

// Initialiser les variables pour les messages
$message = "";
$messageType = "";

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userC = new UserC();

    // Vérifier que tous les champs requis sont définis et non vides
    if (
        isset($_POST["nom"], $_POST["email"], $_POST["mdp"], $_POST["tel"], $_POST["ville"], $_POST["daten"], $_POST["role"]) &&
        !empty($_POST["nom"]) &&
        !empty($_POST["email"]) &&
        !empty($_POST["mdp"]) &&
        !empty($_POST["tel"]) &&
        !empty($_POST["ville"]) &&
        !empty($_POST["daten"]) &&
        !empty($_POST['role'])
    ) {
        // Création de l'objet User avec les données du formulaire
        $user = new User(
            null,
            $_POST['nom'],
            $_POST['email'],
            $_POST['mdp'],
            $_POST['tel'],
            $_POST['ville'],
            $_POST['daten'],
            $_POST['role']
        );

        // Ajouter l'utilisateur via le contrôleur
        $userC->addUser($user);
        header("Location: liste.php");
        exit();
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture | Ajouter un utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" rel="stylesheet">
    <style>
        :root {
            --bleu-ocean: #0a4c8c;
            --bleu-ciel: #3e9bff;
            --bleu-clair: #e1f0ff;
            --vert-vif: #10b981;
            --orange-vif: #f59e0b;
            --rouge: #ef4444;
            --violet: #8b5cf6;
            --blanc-creme: #f8fafc;
            --texte-fonce: #1e293b;
            --texte-clair: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--blanc-creme);
            color: var(--texte-fonce);
            line-height: 1.6;
        }

        .admin-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, var(--bleu-ocean), #0d3a6a);
            color: var(--texte-clair);
            padding: 2rem 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: sticky;
            top: 0;
        }

        .sidebar-header {
            padding: 0 2rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .admin-logo {
            width: 80%;
            max-width: 180px;
        }

        .sidebar-nav {
            flex-grow: 1;
            padding: 1.5rem;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: var(--texte-clair);
            text-decoration: none;
            font-size: 1.05rem;
            font-weight: 500;
            transition: all 0.3s;
            border-radius: 8px;
            margin: 0.25rem 0;
        }

        .sidebar-nav li.active a,
        .sidebar-nav li a:hover {
            background: rgba(255,255,255,0.1);
        }

        .sidebar-nav i {
            margin-right: 1rem;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        h1 {
            font-size: 2rem;
            color: var(--bleu-ocean);
        }

        /* Form Styles */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--texte-fonce);
        }

        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--bleu-ciel);
            box-shadow: 0 0 0 3px rgba(62, 155, 255, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--bleu-ciel);
            color: white;
        }

        .btn-primary:hover {
            background: var(--bleu-ocean);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--texte-fonce);
            margin-right: 1rem;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .message {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .message.error {
            background: #fee2e2;
            color: #ef4444;
            border: 1px solid #fecaca;
        }

        .message.success {
            background: #dcfce7;
            color: #10b981;
            border: 1px solid #bbf7d0;
        }

        @media (max-width: 1024px) {
            .admin-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="back-office/images/logo-worldventure.png" alt="WorldVenture" class="admin-logo">
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Gestion des comptes</a></li>
                    <li><a href="../back/index.html"><i class="fas fa-tags"></i> Offres</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Blog</a></li>
                    <li><a href="#"><i class="fas fa-comment-alt"></i> Réclamation</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="admin-header">
                <h1><i class="fas fa-user-plus"></i> Ajouter un utilisateur</h1>
            </header>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nom">Nom complet</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="mdp">Mot de passe</label>
                        <input type="password" id="mdp" name="mdp" required>
                    </div>

                    <div class="form-group">
                        <label for="tel">Téléphone</label>
                        <input type="tel" id="tel" name="tel" required>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" required>
                    </div>

                    <div class="form-group">
                        <label for="daten">Date de naissance</label>
                        <input type="date" id="daten" name="daten" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Rôle</label>
                        <select id="role" name="role" required>
                            <option value="">Sélectionnez un rôle</option>
                            <option value="admin">Administrateur</option>
                            <option value="user">Utilisateur</option>
                        </select>
                    </div>

                    <div class="action-buttons">
                        <a href="liste.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
