<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$fpdf_url = 'http://www.fpdf.org/en/download/fpdf184.zip';
$zip_file = __DIR__ . '/fpdf.zip';
$extract_path = __DIR__ . '/lib/fpdf';

echo "Starting FPDF installation...\n";

// Create directory if it doesn't exist
if (!file_exists($extract_path)) {
    mkdir($extract_path, 0777, true);
    echo "Created directory: $extract_path\n";
}

// Download FPDF
echo "Downloading FPDF...\n";
if (file_put_contents($zip_file, file_get_contents($fpdf_url))) {
    echo "Download successful\n";
    
    // Extract ZIP file
    $zip = new ZipArchive;
    if ($zip->open($zip_file) === TRUE) {
        $zip->extractTo($extract_path);
        $zip->close();
        echo "Extraction successful\n";
        
        // Clean up
        unlink($zip_file);
        echo "Cleaned up temporary files\n";
        
        // Verify installation
        if (file_exists($extract_path . '/fpdf.php')) {
            echo "FPDF installed successfully!\n";
        } else {
            echo "Error: fpdf.php not found after extraction\n";
        }
    } else {
        echo "Error: Failed to extract ZIP file\n";
    }
} else {
    echo "Error: Failed to download FPDF\n";
}
?> 