<?php
   require_once('C:\xampp\htdocs\user\controller\userC.php');
   require_once('C:\xampp\htdocs\user\model\user.php');

// Vérifier si l'ID est fourni dans l'URL
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo "Erreur : ID d'utilisateur non spécifié.";
    echo "<br><a href='liste.php' class='btn btn-primary'>Retour à la liste</a>";
    exit();
}

$id = $_GET["id"];
$userC = new userC();
$user = $userC->showUser($id);

// Check if user exists
if (!$user) {
    echo "Utilisateur non trouvé.";
    echo "<br><a href='liste.php' class='btn btn-primary'>Retour à la liste</a>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WorldVenture | Dashboard Admin</title>
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

    h2 {
      font-size: 1.6rem;
      margin-bottom: 1.5rem;
      color: var(--bleu-ocean);
      font-weight: 600;
    }

    /* Grille de statistiques avec nouvelles couleurs */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }

    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 12px rgba(10, 76, 140, 0.08);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .stat-card.blue {
      border-left: 4px solid var(--bleu-ciel);
    }
    .stat-card.green {
      border-left: 4px solid var(--vert-vif);
    }
    .stat-card.orange {
      border-left: 4px solid var(--orange-vif);
    }
    .stat-card.violet {
      border-left: 4px solid var(--violet);
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(10, 76, 140, 0.12);
    }

    .stat-card .stat-value {
      font-size: 2.2rem;
      font-weight: 700;
      color: var(--bleu-ocean);
      margin-bottom: 0.25rem;
    }

    .stat-card .stat-label {
      color: #64748b;
      font-size: 0.95rem;
    }

    .stat-card .stat-change {
      display: flex;
      align-items: center;
      margin-top: 0.5rem;
      font-size: 0.9rem;
    }

    .stat-card .stat-change.positive {
      color: var(--vert-vif);
    }

    .stat-card .stat-change.negative {
      color: var(--rouge);
    }

    /* Graphiques */
    .chart-container {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 12px rgba(10, 76, 140, 0.08);
    }

    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    /* Tableaux */
    .data-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 12px rgba(10, 76, 140, 0.08);
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

    /* Badges avec nouvelles couleurs */
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

    /* Boutons */
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

    .btn-sm {
      padding: 0.5rem 0.9rem;
      font-size: 0.85rem;
    }

    .btn-primary {
      background: var(--bleu-ciel);
      color: white;
    }

    .btn-primary:hover {
      background: var(--bleu-ocean);
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--bleu-ciel);
      color: var(--bleu-ciel);
    }

    .btn-outline:hover {
      background: var(--bleu-clair);
    }

    /* Responsive */
    @media (max-width: 1200px) {
      .admin-container {
        grid-template-columns: 1fr;
      }
      
      .sidebar {
        display: none;
      }
      
      h1 {
        font-size: 1.8rem;
      }
      
      .stats-grid {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 768px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .main-content {
        padding: 1.5rem;
      }
    }
    .status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: bold;
    display: inline-block;
    margin: 5px;
}

.delete {
    background-color: #ff4d4d;
    color: white;
}

.success {
    background-color: #4CAF50;
    color: white;
}

.update {
    background-color: #007bff;
    color: white;
}

  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="admin-container">
    <!-- Sidebar modifiée -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <img src="back-office/images/logo-worldventure.png" alt="WorldVenture" class="admin-logo">
      </div>
      
      <nav class="sidebar-nav">
        <ul>
          <li class="active"><a href="#"><i class="fas fa-tachometer-alt"></i> Gestion des comptes  </li>
           <li><a href="../back/index.html"><i class="fas fa-tags"></i> Offres</a></li>
          <li><a href="#"><i class="fas fa-users"></i> Blog</a></li>
          <li><a href="#"><i class="fas fa-comment-alt"></i> Réclamation</a></li>
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
    <h1><i class="fas fa-tachometer-alt"></i> Tableau de Bord</h1>
    <div class="admin-actions">
    <a href="liste.php" class="btn btn-primary">
  <i class="fas fa-sync-alt"></i> Retour à la liste
</a>

    </div>
  </header>

  <!-- Statistiques avec nouvelles couleurs -->

  <!-- End Navbar -->
  <div class="container-fluid py-2">
    <div class="row">
      <div class="col-12">
        <div class="card my-4">
          <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
             </div>
          </div>
          <div class="card-body px-0 pb-2">
            <ul class="list-group">
              <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                <div class="d-flex flex-column">
                  <h2 class="mb-3 text-sm">Détails d'utilisateur : <?php echo htmlspecialchars($user["id"]); ?></h2>

                  <div class="mb-2 text-xs">ID d'utilisateur :
                    <span class="text-dark font-weight-bold ms-sm-2"><?php echo htmlspecialchars($user["id"]); ?></span>
                  </div>

                  <div class="mb-2 text-xs">Nom complet  :
                    <span class="text-dark font-weight-bold ms-sm-2"><?php echo htmlspecialchars($user["nom"]); ?></span>
                  </div>

                  <div class="mb-2 text-xs">Email  :
                    <span class="text-dark font-weight-bold ms-sm-2"><?php echo htmlspecialchars($user["email"]); ?></span>
                  </div>

                  <div class="mb-2 text-xs">Mot de passe :
                    <span class="text-dark font-weight-bold ms-sm-2"><?php echo htmlspecialchars($user["mdp"]); ?></span>
                  </div>

                  <div class="mb-2 text-xs"> Date de naissance :
                    <span class="text-dark font-weight-bold ms-sm-2"><?php echo htmlspecialchars($user["daten"]); ?></span>
                  </div>
                  <div class="mb-2 text-xs"> Telephone :
                    <span class="text-dark font-weight-bold ms-sm-2"><?php echo htmlspecialchars($user["tel"]); ?></span>
                  </div>
                  <div class="mb-2 text-xs">  Ville     :
                    <span class="text-dark font-weight-bold ms-sm-2"><?php echo htmlspecialchars($user["ville"]); ?></span>
                  </div>
                  <div class="mb-2 text-xs"> Role :
                    <span class="text-dark font-weight-bold ms-sm-2"><?php echo htmlspecialchars($user["role"]); ?></span>
                  </div>
                </div>
              </li>
             
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
  <script>
    // Graphique des réservations
    const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
    const reservationsChart = new Chart(reservationsCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
          label: 'Réservations 2023',
          data: [120, 190, 170, 220, 240, 280, 310, 290, 330, 380, 350, 400],
          borderColor: '#3e9bff',
          backgroundColor: 'rgba(62, 155, 255, 0.1)',
          borderWidth: 3,
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });

    // Gestion de la déconnexion
    document.querySelector('.logout-btn').addEventListener('click', function() {
      if(confirm('Voulez-vous vraiment vous déconnecter ?')) {
        window.location.href = 'index.html';
      }
    });
  </script>
</body>
</html>
