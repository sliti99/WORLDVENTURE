<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if (!file_exists('fpdf/fpdf.php')) {
        throw new Exception('Le fichier fpdf.php n\'existe pas dans le dossier fpdf/');
    }

    require('fpdf/fpdf.php');

    // Test de création d'un PDF simple
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Test FPDF');

    // Headers pour le téléchargement
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="test.pdf"');
    
    // Nettoyage du buffer et génération
    ob_clean();
    $pdf->Output('I', 'test.pdf');

} catch (Exception $e) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Erreur de test FPDF: ' . $e->getMessage(),
        'path' => getcwd(),
        'files' => scandir('.')
    ]));
}
?> 