<?php
session_start();
include_once(__DIR__ . '/../../controller/ReclamationController.php');
include_once(__DIR__ . '/../../model/Reclamation.php');

$reclamationController = new ReclamationController();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $id = $_POST['id'];
                $date = $_POST['date'];
                $description = $_POST['description'];
                $etat = $_POST['etat'];
                if ($reclamationController->updateReclamation($id, $date, $description, $etat)) {
                    $_SESSION['success'] = "État de la réclamation mis à jour avec succès!";
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

$reclamations = $reclamationController->getReclamations();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldVenture | Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            font-size: 16px;
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
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
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
            overflow-y: auto;
            padding: 0 1.5rem;
        }

        .sidebar-nav ul {
            list-style: none;
            margin-top: 1rem;
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

        .sidebar-nav li a:hover,
        .sidebar-nav li.active a {
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }

        .sidebar-nav i {
            margin-right: 1rem;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(255,255,255,0.1);
            color: var(--texte-clair);
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        .logout-btn i {
            margin-right: 0.8rem;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            background-color: var(--blanc-creme);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--bleu-ocean);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        h1 i {
            color: var(--bleu-ciel);
        }

        .data-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(10, 76, 140, 0.08);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-header h2 {
            font-size: 1.6rem;
            color: var(--bleu-ocean);
            font-weight: 600;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th {
            background: var(--bleu-clair);
            color: var(--bleu-ocean);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: var(--bleu-clair);
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-badge.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--vert-vif);
        }

        .status-badge.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--orange-vif);
        }

        .btn {
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--bleu-ciel);
            color: white;
        }

        .btn-primary:hover {
            background: var(--bleu-ocean);
        }

        .btn-danger {
            background: var(--rouge);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .modal-content {
            border-radius: 12px;
            overflow: hidden;
        }

        .modal-header {
            background: var(--bleu-ocean);
            color: white;
            border-bottom: none;
        }

        .modal-body {
            padding: 1.5rem;
        }

        @media (max-width: 1200px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="images/logo-worldventure.png" alt="WorldVenture" class="admin-logo">
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="backoffice.html"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                    <li><a href="#"><i class="fas fa-globe-americas"></i> Destinations</a></li>
                    <li><a href="#"><i class="fas fa-tags"></i> Offres</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Blog</a></li>
                    <li class="active"><a href="#"><i class="fas fa-comment-alt"></i> Réclamation</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="admin-header">
                <h1><i class="fas fa-comment-alt"></i> Gestion des Réclamations</h1>
                <div class="admin-actions">
                    <button class="btn btn-primary" onclick="window.location.reload();">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="data-card">
                <div class="card-header">
                    <h2>Liste des Réclamations</h2>
                </div>
                <div class="table-responsive">
                    <table>
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
                                        <span class="status-badge <?php 
                                            echo $reclamation['etat_reclamation'] === 'Résolu' ? 'success' : 
                                                ($reclamation['etat_reclamation'] === 'En cours' ? 'warning' : '');
                                        ?>">
                                            <?php echo $reclamation['etat_reclamation']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateModal<?php echo $reclamation['id_reclamation']; ?>">
                                            <i class="fas fa-edit"></i> Traiter
                                        </button>
                                        <form method="POST" action="" class="d-inline" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réclamation ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $reclamation['id_reclamation']; ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal de mise à jour -->
                                <div class="modal fade" id="updateModal<?php echo $reclamation['id_reclamation']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Traiter la réclamation</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id" value="<?php echo $reclamation['id_reclamation']; ?>">
                                                    <input type="hidden" name="date" value="<?php echo $reclamation['date_reclamation']; ?>">
                                                    <input type="hidden" name="description" value="<?php echo $reclamation['description_reclamation']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Description</label>
                                                        <p class="form-control-static">
                                                            <?php echo htmlspecialchars($reclamation['description_reclamation']); ?>
                                                        </p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="etat" class="form-label">Nouvel état</label>
                                                        <select class="form-select" name="etat" required>
                                                            <option value="En attente" <?php echo $reclamation['etat_reclamation'] === 'En attente' ? 'selected' : ''; ?>>
                                                                En attente
                                                            </option>
                                                            <option value="En cours" <?php echo $reclamation['etat_reclamation'] === 'En cours' ? 'selected' : ''; ?>>
                                                                En cours
                                                            </option>
                                                            <option value="Résolu" <?php echo $reclamation['etat_reclamation'] === 'Résolu' ? 'selected' : ''; ?>>
                                                                Résolu
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="text-end">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (empty($reclamations)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                        <p class="text-muted">Aucune réclamation trouvée</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion de la déconnexion
        document.querySelector('.logout-btn').addEventListener('click', function() {
            if(confirm('Voulez-vous vraiment vous déconnecter ?')) {
                window.location.href = 'index.html';
            }
        });
    </script>
</body>
</html>
