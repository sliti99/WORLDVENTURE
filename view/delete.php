<?php

require_once('C:\xampp\htdocs\user\controller\userC.php');

$userC = new userC();

// Suppression du paiement via l'ID passé en GET
if (isset($_GET['id'])) {
    $userC->deleteUser($_GET['id']);
    header('Location: liste.php');
    exit();
} else {
    echo "Erreur : ID de paiement non spécifié.";
}
?>
