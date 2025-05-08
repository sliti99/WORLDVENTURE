<?php
// Debug - Afficher les erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the necessary userC.php file
require_once('C:\xampp\htdocs\user\controller\userC.php');

try {
    // Create an instance of UserC class
    $user = new userC();
    
    // Nombre d'éléments par page
    $elementsParPage = 3;
    
    // Page courante
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page);
    
    // Récupérer le paramètre de tri
    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
    
    // Fetch the list of users
    $tab = $user->listUsers();
    
    // Trier le tableau selon la date de naissance si demandé
    if ($sort === 'date_asc') {
        usort($tab, function($a, $b) {
            return strtotime($a['daten']) - strtotime($b['daten']);
        });
    } elseif ($sort === 'date_desc') {
        usort($tab, function($a, $b) {
            return strtotime($b['daten']) - strtotime($a['daten']);
        });
    }
    
    // Calculer le nombre total de pages
    $nombreTotal = count($tab);
    $nombrePages = ceil($nombreTotal / $elementsParPage);
    
    // Limiter la page courante au nombre total de pages
    $page = min($page, $nombrePages);
    
    // Calculer l'index de début pour la page courante
    $indexDebut = ($page - 1) * $elementsParPage;
    
    // Extraire les éléments pour la page courante
    $utilisateursPage = array_slice($tab, $indexDebut, $elementsParPage);
    
    // Debug - Vérifier les données
    echo "<!-- Debug données: \n";
    var_dump($tab);
    echo "\n-->";
    
} catch (Exception $e) {
    echo "<!-- Erreur: " . $e->getMessage() . " -->";
    $tab = array();
    $utilisateursPage = array();
    $nombrePages = 1;
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
      padding: 5px 10px;
      border-radius: 15px;
      font-weight: bold;
      display: inline-block;
      margin: 5px;
    }

    .status-badge a {
      text-decoration: none;
      color: white;
    }

    .delete {
      background-color: #ff4d4d;
    }

    .success {
      background-color: #4CAF50;
    }

    .update {
      background-color: #007bff;
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

    .btn-outline.active {
      background: var(--bleu-ciel);
      color: white;
    }

    .sort-buttons {
      display: flex;
      gap: 0.5rem;
    }

    .sort-buttons .btn {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
    }

    .sort-buttons .btn i {
      margin-right: 0.3rem;
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

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
      background-color: white;
      margin: 10% auto;
      padding: 20px;
      width: 70%;
      max-width: 700px;
      border-radius: 12px;
      position: relative;
    }

    .close {
      position: absolute;
      right: 20px;
      top: 10px;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover {
      color: var(--bleu-ciel);
    }

    .chart-container {
      margin-top: 20px;
    }

    /* Styles pour la pagination */
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 2rem;
      gap: 0.5rem;
    }

    .pagination-btn {
      padding: 0.5rem 1rem;
      border: 1px solid var(--bleu-ciel);
      background: white;
      color: var(--bleu-ciel);
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .pagination-btn:hover {
      background: var(--bleu-clair);
    }

    .pagination-btn.active {
      background: var(--bleu-ciel);
      color: white;
      border-color: var(--bleu-ciel);
    }

    .pagination-btn.disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .pagination-info {
      color: var(--texte-fonce);
      font-size: 0.9rem;
      margin: 0 1rem;
    }

    @media (max-width: 768px) {
      .pagination {
        flex-wrap: wrap;
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
          <a href="export_pdf.php" class="btn btn-primary" style="margin-right: 10px;">
            <i class="fas fa-file-pdf"></i> Exporter CSV
          </a>
          <button class="btn btn-primary" onclick="openStatsModal()">
            <i class="fas fa-chart-pie"></i> Statistiques
          </button>
          <a href="liste.php" class="btn btn-primary">
            <i class="fas fa-sync-alt"></i> Actualiser
          </a>
        </div>
      </header>

      <!-- Liste des utilisateurs -->
      <div class="data-card">
        <div class="card-header">
          <h2>Liste des comptes</h2>
          <div class="filters" style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center;">
            <div class="search-container">
              <input type="text" id="searchInput" placeholder="Rechercher par nom..." style="
                padding: 0.5rem;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                width: 300px;
                font-size: 0.95rem;
              ">
            </div>
            <div class="month-filter">
              <select id="monthFilter" style="
                padding: 0.5rem;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                font-size: 0.95rem;
                background-color: white;
              ">
                <option value="">Tous les mois</option>
                <option value="01">Janvier</option>
                <option value="02">Février</option>
                <option value="03">Mars</option>
                <option value="04">Avril</option>
                <option value="05">Mai</option>
                <option value="06">Juin</option>
                <option value="07">Juillet</option>
                <option value="08">Août</option>
                <option value="09">Septembre</option>
                <option value="10">Octobre</option>
                <option value="11">Novembre</option>
                <option value="12">Décembre</option>
              </select>
            </div>
            <div class="sort-buttons" style="display: flex; gap: 0.5rem;">
              <a href="?sort=date_asc<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                 class="btn btn-outline <?php echo $sort === 'date_asc' ? 'active' : ''; ?>">
                <i class="fas fa-sort-amount-up-alt"></i> Date ↑
              </a>
              <a href="?sort=date_desc<?php echo isset($_GET['page']) ? '&page=' . $_GET['page'] : ''; ?>" 
                 class="btn btn-outline <?php echo $sort === 'date_desc' ? 'active' : ''; ?>">
                <i class="fas fa-sort-amount-down"></i> Date ↓
              </a>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table id="usersTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Mot de passe</th>
                <th>Téléphone</th>
                <th>Ville</th>
                <th>Date de naissance</th>
                <th>Rôle</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($utilisateursPage as $user) { ?>
                <tr>
                  <td><?php echo htmlspecialchars($user['id']); ?></td>
                  <td><?php echo htmlspecialchars($user['nom']); ?></td>
                  <td><?php echo htmlspecialchars($user['email']); ?></td>
                  <td><?php echo htmlspecialchars($user['mdp']); ?></td>
                  <td><?php echo htmlspecialchars($user['tel']); ?></td>
                  <td><?php echo htmlspecialchars($user['ville']); ?></td>
                  <td><?php echo htmlspecialchars($user['daten']); ?></td>
                  <td><?php echo htmlspecialchars($user['role']); ?></td>
                  <td>
                    <span class="status-badge delete">
                      <a href="delete.php?id=<?php echo htmlspecialchars($user['id']); ?>">Supprimer</a>
                    </span>
                    <span class="status-badge success">
                      <a href="show.php?id=<?php echo htmlspecialchars($user['id']); ?>">Afficher</a>
                    </span>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
          <?php if ($nombrePages > 1): ?>
            <!-- Bouton Précédent -->
            <a href="?page=<?php echo max(1, $page - 1); ?>" 
               class="pagination-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>">
              <i class="fas fa-chevron-left"></i> Précédent
            </a>

            <!-- Numéros de page -->
            <?php for ($i = 1; $i <= $nombrePages; $i++): ?>
              <a href="?page=<?php echo $i; ?>" 
                 class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>

            <!-- Bouton Suivant -->
            <a href="?page=<?php echo min($nombrePages, $page + 1); ?>" 
               class="pagination-btn <?php echo $page >= $nombrePages ? 'disabled' : ''; ?>">
              Suivant <i class="fas fa-chevron-right"></i>
            </a>

            <!-- Information sur la pagination -->
            <span class="pagination-info">
              Page <?php echo $page; ?> sur <?php echo $nombrePages; ?>
              (<?php echo $nombreTotal; ?> utilisateurs)
            </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Modal Statistiques -->
      <div id="statsModal" class="modal">
        <div class="modal-content">
          <span class="close" onclick="closeStatsModal()">&times;</span>
          <h2>Répartition des utilisateurs par rôle</h2>
          <div class="chart-container" style="position: relative; height:400px; width:100%;">
            <canvas id="roleChart"></canvas>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
  <script>
    // Gestion de la déconnexion
    document.querySelector('.logout-btn').addEventListener('click', function() {
      if(confirm('Voulez-vous vraiment vous déconnecter ?')) {
        window.location.href = 'index.html';
      }
    });

    // Fonction de recherche et filtrage combinés
    function filterTable() {
      const searchText = document.getElementById('searchInput').value.toLowerCase();
      const selectedMonth = document.getElementById('monthFilter').value;
      const table = document.getElementById('usersTable');
      const rows = table.getElementsByTagName('tr');

      for (let i = 1; i < rows.length; i++) {
        const nameCell = rows[i].getElementsByTagName('td')[1];
        const dateCell = rows[i].getElementsByTagName('td')[6];
        let showRow = true;

        if (nameCell && dateCell) {
          const name = nameCell.textContent.toLowerCase();
          const date = dateCell.textContent;
          
          // Vérifier le filtre de recherche par nom
          if (!name.includes(searchText)) {
            showRow = false;
          }

          // Vérifier le filtre de mois si un mois est sélectionné
          if (selectedMonth && date) {
            try {
              const birthDate = new Date(date);
              const birthMonth = String(birthDate.getMonth() + 1).padStart(2, '0');
              if (birthMonth !== selectedMonth) {
                showRow = false;
              }
            } catch (e) {
              console.error('Erreur de parsing de date:', e);
            }
          }

          rows[i].style.display = showRow ? '' : 'none';
        }
      }
    }

    // Écouteurs d'événements pour la recherche et le filtre
    document.getElementById('searchInput').addEventListener('keyup', filterTable);
    document.getElementById('monthFilter').addEventListener('change', filterTable);

    // Fonction pour ouvrir la modal
    function openStatsModal() {
      const modal = document.getElementById('statsModal');
      if (modal) {
        modal.style.display = 'block';
        setTimeout(generateRoleChart, 100); // Délai pour s'assurer que la modal est visible
      }
    }

    // Fonction pour fermer la modal
    function closeStatsModal() {
      const modal = document.getElementById('statsModal');
      if (modal) {
        modal.style.display = 'none';
      }
    }

    // Fermer la modal si on clique en dehors
    window.onclick = function(event) {
      const modal = document.getElementById('statsModal');
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }

    // Générer le pie chart
    function generateRoleChart() {
      const users = <?php 
        // Debug des données
        echo "/* Debug: ";
        var_dump($tab);
        echo " */";

        $roles = array();
        if (!empty($tab)) {
          foreach ($tab as $user) {
            $role = isset($user['role']) && !empty($user['role']) ? $user['role'] : 'Non défini';
            if (!isset($roles[$role])) {
              $roles[$role] = 0;
            }
            $roles[$role]++;
          }
        } else {
          $roles['Aucune donnée'] = 1;
        }
        echo json_encode($roles);
      ?>;

      console.log('Données des rôles brutes:', users);

      // Si les données sont vides, afficher un message
      if (Object.keys(users).length === 0) {
        console.error('Aucune donnée de rôle disponible');
        return;
      }

      const canvas = document.getElementById('roleChart');
      if (!canvas) {
        console.error('Canvas roleChart non trouvé!');
        return;
      }

      // Détruire le graphique existant s'il y en a un
      if (window.roleChart instanceof Chart) {
        window.roleChart.destroy();
      }

      try {
        window.roleChart = new Chart(canvas, {
          type: 'pie',
          data: {
            labels: Object.keys(users),
            datasets: [{
              data: Object.values(users),
              backgroundColor: [
                '#0a4c8c',
                '#3e9bff',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#8b5cf6'
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right',
                labels: {
                  font: {
                    size: 14
                  }
                }
              },
              title: {
                display: true,
                text: 'Distribution des rôles',
                font: {
                  size: 16
                }
              }
            }
          }
        });
        console.log('Nouveau graphique créé avec succès');
      } catch (error) {
        console.error('Erreur lors de la création du graphique:', error);
      }
    }

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Page chargée, vérification des éléments...');
      const modal = document.getElementById('statsModal');
      const btn = document.querySelector('[onclick="openStatsModal()"]');
      const canvas = document.getElementById('roleChart');
      
      console.log('Modal:', modal);
      console.log('Bouton:', btn);
      console.log('Canvas:', canvas);
    });
  </script>
</body>
</html>
