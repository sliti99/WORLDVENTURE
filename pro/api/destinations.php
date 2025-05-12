<?php
// Set headers for API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
function getDbConnection() {
    $host = 'localhost';
    $db = 'your_database';
    $user = 'your_username';
    $pass = 'your_password';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

// Handle API requests
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        getDestinations();
        break;
    case 'POST':
        addDestination();
        break;
    case 'PUT':
        updateDestination();
        break;
    case 'DELETE':
        deleteDestination();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function getDestinations() {
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM destinations");
        $stmt->execute();
        $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($destinations);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function addDestination() {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($data['name']) || !isset($data['description'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO destinations (name, description) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['description']]);
        
        http_response_code(201);
        echo json_encode(['message' => 'Destination added successfully', 'id' => $conn->lastInsertId()]);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function updateDestination() {
    // Implementation for updating destinations
}

function deleteDestination() {
    // Implementation for deleting destinations
}
?>
