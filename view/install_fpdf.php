<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Définir les chemins
$fpdf_dir = __DIR__ . '/fpdf';

// Créer le dossier fpdf s'il n'existe pas
if (!file_exists($fpdf_dir)) {
    mkdir($fpdf_dir, 0777, true);
}

// URL de téléchargement de FPDF (version 1.84)
$fpdf_url = 'http://www.fpdf.org/en/download/fpdf184.zip';
$zip_file = $fpdf_dir . '/fpdf.zip';

echo "Début de l'installation de FPDF...<br>";
echo "Dossier de destination : " . $fpdf_dir . "<br>";

// Télécharger le fichier ZIP
echo "Téléchargement de FPDF...<br>";
$zip_content = file_get_contents($fpdf_url);
if ($zip_content === FALSE) {
    die("Erreur lors du téléchargement de FPDF");
}

// Sauvegarder le ZIP
if (file_put_contents($zip_file, $zip_content) === FALSE) {
    die("Erreur lors de la sauvegarde du fichier ZIP");
}

echo "Fichier ZIP téléchargé avec succès.<br>";

// Extraire le ZIP
$zip = new ZipArchive;
if ($zip->open($zip_file) === TRUE) {
    // Extraire dans un dossier temporaire
    $temp_dir = $fpdf_dir . '/temp';
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir);
    }
    
    $zip->extractTo($temp_dir);
    $zip->close();
    
    echo "Fichier ZIP extrait avec succès.<br>";
    
    // Copier les fichiers nécessaires
    $source_dir = $temp_dir . '/fpdf184';
    if (is_dir($source_dir)) {
        // Copier tous les fichiers de fpdf184
        foreach (scandir($source_dir) as $file) {
            if ($file != "." && $file != "..") {
                $source = $source_dir . '/' . $file;
                $dest = $fpdf_dir . '/' . $file;
                if (is_dir($source)) {
                    // Si c'est un dossier (comme font/), le copier récursivement
                    if (!file_exists($dest)) {
                        mkdir($dest);
                    }
                    foreach (scandir($source) as $subfile) {
                        if ($subfile != "." && $subfile != "..") {
                            copy($source . '/' . $subfile, $dest . '/' . $subfile);
                        }
                    }
                } else {
                    // Copier le fichier
                    copy($source, $dest);
                }
            }
        }
        echo "Fichiers copiés avec succès.<br>";
    } else {
        die("Dossier source non trouvé après extraction");
    }
    
    // Nettoyer
    array_map('unlink', glob($temp_dir . '/fpdf184/*.*'));
    rmdir($temp_dir . '/fpdf184');
    rmdir($temp_dir);
    unlink($zip_file);
    
    echo "Nettoyage effectué.<br>";
    
    // Vérifier l'installation
    if (file_exists($fpdf_dir . '/fpdf.php')) {
        echo "<br>Installation réussie! FPDF est prêt à être utilisé.<br>";
        echo "Chemin du fichier fpdf.php : " . $fpdf_dir . '/fpdf.php';
    } else {
        echo "<br>ERREUR: Le fichier fpdf.php n'a pas été trouvé après l'installation.";
    }
} else {
    die("Erreur lors de l'ouverture du fichier ZIP");
}
?> 