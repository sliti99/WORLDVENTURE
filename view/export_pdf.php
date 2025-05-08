<?php
require_once('C:\xampp\htdocs\user\controller\userC.php');

try {
    // Création de l'instance UserC
    $user = new UserC();
    
    // Récupération de la liste des utilisateurs
    $liste = $user->listUsers();
    
    // Définir l'en-tête HTTP pour un fichier CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=liste_utilisateurs.csv');
    
    // Créer le flux de sortie
    $output = fopen('php://output', 'w');
    
    // Ajouter le BOM UTF-8 pour Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // En-têtes des colonnes
    fputcsv($output, array(
        'ID',
        'Nom',
        'Email',
        'Tel',
        'Ville',
        'Date Naissance',
        'Role'
    ), ';');
    
    // Contenu
    foreach($liste as $user) {
        fputcsv($output, array(
            $user['id'],
            $user['nom'],
            $user['email'],
            $user['tel'],
            $user['ville'],
            $user['daten'],
            $user['role']
        ), ';');
    }
    
    // Fermer le flux
    fclose($output);
    
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
?> 