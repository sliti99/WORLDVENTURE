<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function checkDirectory($dir) {
    return [
        'exists' => file_exists($dir),
        'writable' => is_writable($dir),
        'permissions' => substr(sprintf('%o', fileperms($dir)), -4),
        'contents' => file_exists($dir) ? scandir($dir) : []
    ];
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Vérification de la configuration PDF</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; }
    </style>
</head>
<body>
    <h1>Diagnostic de la génération PDF</h1>

    <h2>1. Vérification des chemins</h2>
    <?php
    $base_path = __DIR__;
    $paths_to_check = [
        'Current Directory' => __DIR__,
        'Parent Directory' => dirname(__DIR__),
        'Root Directory' => dirname(dirname(__DIR__)),
        'FPDF Directory' => __DIR__ . '/fpdf',
        'Parent FPDF Directory' => dirname(__DIR__) . '/fpdf',
        'Root FPDF Directory' => dirname(dirname(__DIR__)) . '/fpdf'
    ];

    foreach ($paths_to_check as $name => $path) {
        $check = checkDirectory($path);
        echo "<h3>$name: $path</h3>";
        echo "<ul>";
        echo "<li>Existe: " . ($check['exists'] ? "✅" : "❌") . "</li>";
        if ($check['exists']) {
            echo "<li>Permissions: " . $check['permissions'] . "</li>";
            echo "<li>Accessible en écriture: " . ($check['writable'] ? "✅" : "❌") . "</li>";
            echo "<li>Contenu:<pre>" . print_r($check['contents'], true) . "</pre></li>";
        }
        echo "</ul>";
    }
    ?>

    <h2>2. Recherche de fpdf.php</h2>
    <?php
    $possible_fpdf_paths = [
        __DIR__ . '/fpdf/fpdf.php',
        __DIR__ . '/../fpdf/fpdf.php',
        __DIR__ . '/../../fpdf/fpdf.php',
        __DIR__ . '/lib/fpdf/fpdf.php'
    ];

    $fpdf_found = false;
    foreach ($possible_fpdf_paths as $path) {
        echo "<div>";
        if (file_exists($path)) {
            echo "✅ Trouvé à: $path";
            $fpdf_found = true;
        } else {
            echo "❌ Non trouvé à: $path";
        }
        echo "</div>";
    }
    ?>

    <h2>3. Test de création PDF</h2>
    <?php
    if ($fpdf_found) {
        try {
            require($path);
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(40, 10, 'Test FPDF');
            
            $test_file = __DIR__ . '/test_verification.pdf';
            $pdf->Output('F', $test_file);
            
            echo "<div class='success'>✅ PDF de test créé avec succès: $test_file</div>";
            echo "<div><a href='test_verification.pdf' target='_blank'>Voir le PDF de test</a></div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur lors de la création du PDF: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='error'>❌ Impossible de tester la création de PDF car FPDF n'a pas été trouvé</div>";
    }
    ?>

    <h2>4. Configuration PHP</h2>
    <?php
    $config_to_check = [
        'memory_limit',
        'max_execution_time',
        'upload_max_filesize',
        'post_max_size',
        'display_errors',
        'error_reporting'
    ];

    echo "<ul>";
    foreach ($config_to_check as $config) {
        echo "<li>$config: " . ini_get($config) . "</li>";
    }
    echo "</ul>";
    ?>

    <h2>5. Extensions PHP</h2>
    <?php
    $required_extensions = ['zlib', 'mbstring'];
    foreach ($required_extensions as $ext) {
        echo "<div>";
        if (extension_loaded($ext)) {
            echo "✅ $ext est chargé";
        } else {
            echo "❌ $ext n'est pas chargé";
        }
        echo "</div>";
    }
    ?>

    <h2>6. Test d'écriture</h2>
    <?php
    $test_dirs = [__DIR__, dirname(__DIR__)];
    foreach ($test_dirs as $dir) {
        $test_file = $dir . '/write_test.txt';
        echo "<h3>Test dans $dir</h3>";
        try {
            $handle = fopen($test_file, 'w');
            if ($handle) {
                fwrite($handle, 'Test d\'écriture');
                fclose($handle);
                unlink($test_file);
                echo "<div class='success'>✅ Test d'écriture réussi</div>";
            } else {
                echo "<div class='error'>❌ Impossible d'ouvrir le fichier en écriture</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erreur lors du test d'écriture: " . $e->getMessage() . "</div>";
        }
    }
    ?>
</body>
</html> 