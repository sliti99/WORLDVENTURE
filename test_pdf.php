<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/lib/fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, 'WorldVenture', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Confirmation de Reservation', 0, 1, 'C');
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | WorldVenture - ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
}

try {
    echo "Starting PDF generation test...\n";
    
    // Create test data
    $data = [
        'Numero de reservation' => 'RES-123456',
        'Client' => 'John Doe',
        'Destination' => 'Paris, France',
        'Date de depart' => '15 Mars 2024',
        'Date de retour' => '22 Mars 2024',
        'Nombre de voyageurs' => '2 adultes',
        'Prix total' => '1500 EUR',
        'Statut' => 'Confirme'
    ];
    
    // Create PDF
    $pdf = new PDF();
    $pdf->AddPage();
    
    // Add reservation details
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Details de la Reservation:', 0, 1);
    $pdf->Ln(5);
    
    foreach ($data as $key => $value) {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, 8, $key . ':', 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, $value, 0);
        $pdf->Ln();
    }
    
    // Generate filename
    $filename = 'test_reservation_' . time() . '.pdf';
    
    // Save PDF
    $pdf->Output('F', $filename);
    
    echo "PDF generated successfully!\n";
    echo "File: $filename\n";
    echo "Size: " . filesize($filename) . " bytes\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 