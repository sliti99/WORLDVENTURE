<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!class_exists('FPDF')) {
    require_once(__DIR__ . '/lib/fpdf/fpdf.php');
}

class PDF extends FPDF {
    function Header() {
        // Simple text-based header without images
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

    function AddReservationDetails($data) {
        $this->SetFont('Arial', '', 12);
        
        // Add a decorative line
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.5);
        
        // Format the reservation details in a table-like structure
        $this->SetFillColor(240, 240, 240);
        $this->Cell(0, 10, 'Details de la Reservation', 0, 1, 'L', true);
        $this->Ln(5);
        
        foreach ($data as $key => $value) {
            // Left column: key
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(50, 8, ucfirst($key) . ':', 0);
            
            // Right column: value
            $this->SetFont('Arial', '', 11);
            $this->Cell(0, 8, $value, 0);
            $this->Ln();
        }
        
        // Add terms and conditions
        $this->Ln(10);
        $this->SetFont('Arial', 'I', 10);
        $this->MultiCell(0, 5, 'Merci d\'avoir choisi WorldVenture pour votre voyage. Ce document sert de confirmation de votre reservation. Veuillez le conserver precieusement.');
    }
}

function generatePDF($data) {
    // Create PDF instance
    $pdf = new PDF();
    $pdf->AddPage();
    
    // Add reservation details
    $pdf->AddReservationDetails($data);
    
    // Generate unique filename
    $filename = 'reservation_' . time() . '.pdf';
    
    // Save PDF
    $pdf->Output('F', $filename);
    
    return $filename;
}

try {
    // Check if this is a direct call with data
    if (isset($testData)) {
        $data = $testData;
    } else {
        // Get reservation data from POST
        $raw_data = file_get_contents('php://input');
        $data = json_decode($raw_data, true);
    }
    
    if (!$data) {
        throw new Exception('Aucune donnee de reservation recue');
    }
    
    // Generate the PDF
    $filename = generatePDF($data);
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'message' => 'PDF genere avec succes'
    ]);
    
} catch (Exception $e) {
    // Return error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la generation du PDF: ' . $e->getMessage()
    ]);
}
?> 