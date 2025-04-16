<?php
session_start();
include_once(__DIR__ . '/../../controller/ReclamationController.php');
include_once(__DIR__ . '/../../model/Reclamation.php');

// Créer la table si elle n'existe pas
try {
    $db = new config();
    $conn = $db->getConnexion();
    
    $sql = "CREATE TABLE IF NOT EXISTS reclamation (
        id_reclamation INT AUTO_INCREMENT PRIMARY KEY,
        date_reclamation DATETIME NOT NULL,
        description_reclamation TEXT NOT NULL,
        etat_reclamation VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $conn->exec($sql);
} catch (PDOException $e) {
    error_log("Erreur création table: " . $e->getMessage());
    $_SESSION['errors'] = ["Erreur de configuration de la base de données: " . $e->getMessage()];
}

$reclamationController = new ReclamationController();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $date = date('Y-m-d H:i:s');
                    $description = $_POST['description'];
                    $etat = 'En attente';
                    $reclamation = new Reclamation($date, $description, $etat);
                    if ($reclamationController->addReclamation($reclamation)) {
                        $_SESSION['success'] = "Réclamation ajoutée avec succès!";
                    }
                } catch (Exception $e) {
                    $_SESSION['errors'] = ["Erreur: " . $e->getMessage()];
                }
                break;
            case 'update':
                $id = $_POST['id'];
                $date = $_POST['date'];
                $description = $_POST['description'];
                $etat = $_POST['etat'];
                if ($reclamationController->updateReclamation($id, $date, $description, $etat)) {
                    $_SESSION['success'] = "Réclamation mise à jour avec succès!";
                }
                break;
            case 'delete':
                $id = $_POST['id'];
                if ($reclamationController->deleteReclamation($id)) {
                    $_SESSION['success'] = "Réclamation supprimée avec succès!";
                }
                break;
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$editReclamation = null;
if (isset($_GET['edit'])) {
    $editReclamation = $reclamationController->getReclamationById($_GET['edit']);
}

$reclamations = $reclamationController->getReclamations();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture - Réclamations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            height: 50%;
            background-image: url('background.jpg');
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
            height: 50%;
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

        .slogan {
            font-size: 3.2rem;
            font-weight: 900;
            color: #cceeff;
            text-shadow: 2px 2px 8px #000000;
            text-align: center;
            margin-top: 6rem;
            position: relative;
            padding: 0 2rem;
        }

        .main-nav {
            display: flex;
            justify-content: center;
            margin: 4rem auto 2rem;
            width: 100%;
        }

        .main-nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .main-nav-links a {
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            padding: 1.2rem 2.5rem;
            border-radius: 2rem;
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            border: 2px solid #003366;
        }

        .main-nav-links a:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            background: linear-gradient(135deg, #00bfae, #0099cc);
        }

        .login-btn {
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            padding: 1rem 2rem;
            border-radius: 2rem;
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            border: 2px solid #003366;
            margin-top: 0.5rem;
        }

        .login-btn:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            background: linear-gradient(135deg, #00bfae, #0099cc);
        }

        .reclamation-section {
            margin: 20rem auto 5rem;
            max-width: 1200px;
            text-align: center;
            padding: 3rem;
            background: linear-gradient(135deg, #4e79b7, #1e4e8e);
            border-radius: 2rem;
            backdrop-filter: blur(6px);
            animation: fadeIn 2s ease;
            border: 2px solid #003366;
        }

        .form-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 1rem;
        }

        .btn-custom {
            padding: 10px 30px;
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, #00bfae, #0099cc);
        }

        .table-custom {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 1rem;
            overflow: hidden;
            margin-top: 2rem;
        }

        .table-custom th {
            background: linear-gradient(135deg, #1e90ff, #0099cc);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .reclamation-section {
                margin: 16rem auto 3rem;
                padding: 2rem;
            }
            .main-nav-links {
                flex-wrap: wrap;
            }
            .main-nav-links a {
                font-size: 1.1rem;
                padding: 1rem 1.8rem;
            }
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: #dc3545;
            color: white;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            transform: translateX(150%);
            transition: transform 0.3s ease-in-out;
            font-size: 1rem;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.slide-out {
            transform: translateX(150%);
        }
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>
    
    <header>
        <img src="logo.png" alt="WorldVenture Logo" class="logo">
        <a href="#" class="login-btn">Se connecter</a>
    </header>

    <h1 class="slogan">Your next adventure starts here</h1>

    <nav class="main-nav">
        <div class="main-nav-links">
            <a href="#">Destinations</a>
            <a href="#">Nos offres</a>
            <a href="#">Blog</a>
            <a href="#">Contact</a>
            <a href="#" class="active">Réclamation</a>
        </div>
    </nav>

    <section class="reclamation-section">
        <div class="container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        <?php 
                        foreach ($_SESSION['errors'] as $error) {
                            echo "<li>$error</li>";
                        }
                        unset($_SESSION['errors']);
                        ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <h2 class="mb-4" style="color: #222;"><?php echo $editReclamation ? 'Modifier la réclamation' : 'Nouvelle réclamation'; ?></h2>
                
                <form method="POST" action="" id="reclamationForm">
                    <input type="hidden" name="action" value="<?php echo $editReclamation ? 'update' : 'add'; ?>">
                    <?php if ($editReclamation): ?>
                        <input type="hidden" name="id" value="<?php echo $editReclamation['id_reclamation']; ?>">
                        <input type="hidden" name="date" value="<?php echo $editReclamation['date_reclamation']; ?>">
                        <input type="hidden" name="etat" value="<?php echo $editReclamation['etat_reclamation']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <textarea 
                            class="form-control" 
                            name="description" 
                            rows="5" 
                            placeholder="Décrivez votre réclamation..."
                        ><?php echo $editReclamation ? $editReclamation['description_reclamation'] : ''; ?></textarea>
                        <div id="char-counter" class="text-muted mt-1" style="font-size: 0.875rem;">25 caractères restants</div>
                        <div id="error-message" class="text-danger mt-1" style="font-size: 0.875rem;"></div>
                    </div>

                    <button type="submit" class="btn btn-custom">
                        <?php echo $editReclamation ? 'Mettre à jour' : 'Envoyer'; ?>
                    </button>
                    <?php if ($editReclamation): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary ms-2">Annuler</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!empty($reclamations)): ?>
                <div class="table-responsive mt-5">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>État</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reclamations as $reclamation): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($reclamation['date_reclamation'])); ?></td>
                                    <td><?php echo htmlspecialchars($reclamation['description_reclamation']); ?></td>
                                    <td>
                                        <span class="badge badge-custom bg-<?php 
                                            echo $reclamation['etat_reclamation'] === 'Résolu' ? 'success' : 
                                                ($reclamation['etat_reclamation'] === 'En cours' ? 'warning' : 'secondary');
                                        ?>">
                                            <?php echo $reclamation['etat_reclamation']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?edit=<?php echo $reclamation['id_reclamation']; ?>" class="btn btn-sm btn-custom">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $reclamation['id_reclamation']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ajout de la div de notification -->
    <div id="notification" class="notification"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const form = document.getElementById('reclamationForm');
        const description = document.querySelector('textarea[name="description"]');
        const errorMessage = document.getElementById('error-message');
        const charCounter = document.getElementById('char-counter');
        const notification = document.getElementById('notification');
        const maxLength = 25;

        // Fonction pour afficher la notification
        function showNotification(message) {
            notification.textContent = message;
            notification.classList.add('show');
            
            // Retirer la notification après 3 secondes
            setTimeout(() => {
                notification.classList.add('slide-out');
                setTimeout(() => {
                    notification.classList.remove('show', 'slide-out');
                }, 300);
            }, 3000);
        }

        // Fonction pour mettre à jour le compteur et la validation
        function updateCounter() {
            const remaining = maxLength - description.value.length;
            charCounter.textContent = `${remaining} caractère(s) restant(s)`;
            
            // Mise à jour des styles selon le nombre de caractères restants
            if (remaining < 0) {
                charCounter.style.color = '#dc3545';
                description.style.borderColor = '#dc3545';
            } else if (remaining <= 5) {
                charCounter.style.color = '#ffc107';
                description.style.borderColor = '#ffc107';
            } else {
                charCounter.style.color = '#6c757d';
                description.style.borderColor = '#ced4da';
            }
        }

        // Fonction de validation
        function validateDescription() {
            const descriptionValue = description.value.trim();
            
            // Réinitialiser les styles
            description.style.borderColor = '#ced4da';
            
            // Vérification si vide
            if (descriptionValue === '') {
                description.style.borderColor = '#dc3545';
                description.classList.add('shake');
                showNotification('Le champ de description ne peut pas être vide');
                
                // Retirer la classe d'animation après l'animation
                setTimeout(() => {
                    description.classList.remove('shake');
                }, 500);
                
                return false;
            }
            
            // Vérification de la longueur
            if (descriptionValue.length > maxLength) {
                description.style.borderColor = '#dc3545';
                showNotification(`La description ne doit pas dépasser ${maxLength} caractères`);
                return false;
            }
            
            return true;
        }

        // Validation en temps réel
        description.addEventListener('input', function() {
            updateCounter();
            validateDescription();
        });

        // Validation à la soumission
        form.addEventListener('submit', function(e) {
            if (!validateDescription()) {
                e.preventDefault();
                description.focus();
            }
        });

        // Style pour l'animation de secousse
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }
            .shake {
                animation: shake 0.3s ease-in-out;
            }
        `;
        document.head.appendChild(style);

        // Initialisation du compteur
        updateCounter();
    </script>
</body>
</html>
