<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Vérification de l'installation FPDF</h1>";

// Vérifier les extensions PHP
echo "<h2>Extensions PHP</h2>";
$required_extensions = ['zlib', 'mbstring'];
foreach ($required_extensions as $ext) {
    echo "Extension $ext: " . (extension_loaded($ext) ? "✅ Installée" : "❌ Non installée") . "<br>";
}

// Vérifier les chemins possibles
echo "<h2>Recherche de FPDF</h2>";
$possible_paths = [
    __DIR__ . '/fpdf/fpdf.php',
    __DIR__ . '/../fpdf/fpdf.php',
    __DIR__ . '/../../fpdf/fpdf.php',
    __DIR__ . '/lib/fpdf/fpdf.php',
    __DIR__ . '/../lib/fpdf/fpdf.php',
    __DIR__ . '/../../lib/fpdf/fpdf.php'
];

$fpdf_found = false;
foreach ($possible_paths as $path) {
    echo "Vérification de $path: " . (file_exists($path) ? "✅ Trouvé" : "❌ Non trouvé") . "<br>";
    if (file_exists($path)) {
        $fpdf_found = true;
        $fpdf_path = $path;
    }
}

if ($fpdf_found) {
    echo "<br>✅ FPDF trouvé à: $fpdf_path<br>";
    
    // Tester la création d'un PDF
    echo "<h2>Test de création PDF</h2>";
    try {
        require($fpdf_path);
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(40, 10, 'Test FPDF');
        
        $test_file = __DIR__ . '/test.pdf';
        $pdf->Output('F', $test_file);
        
        echo "✅ PDF de test créé avec succès: $test_file<br>";
        echo "<a href='test.pdf' target='_blank'>Voir le PDF de test</a>";
    } catch (Exception $e) {
        echo "❌ Erreur lors de la création du PDF: " . $e->getMessage() . "<br>";
    }
} else {
    echo "<br>❌ FPDF non trouvé dans les chemins recherchés<br>";
    
    // Afficher les informations de débogage
    echo "<h2>Informations de débogage</h2>";
    echo "Répertoire courant: " . __DIR__ . "<br>";
    echo "Contenu du répertoire courant:<br>";
    echo "<pre>";
    print_r(scandir(__DIR__));
    echo "</pre>";
}

// Vérifier les permissions
echo "<h2>Permissions</h2>";
$dirs_to_check = [
    __DIR__,
    __DIR__ . '/fpdf',
    dirname(__DIR__)
];

foreach ($dirs_to_check as $dir) {
    if (file_exists($dir)) {
        echo "Permissions pour $dir: " . substr(sprintf('%o', fileperms($dir)), -4) . "<br>";
    }
}

// Vérifier la configuration PHP
echo "<h2>Configuration PHP</h2>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
?> 