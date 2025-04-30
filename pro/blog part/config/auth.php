<?php
// Authentication functions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the Database class
require_once __DIR__ . '/../models/Database.php';

// User login function
function login($email, $password) {
    $db = Database::getInstance()->getConnection();
    
    try {
        // Check user credentials
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Verify password (using password_verify with hashed passwords for security)
            // For this demo, we're using the raw password comparison as shown in the DB dump
            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                // Set session variables upon successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                return ['success' => true, 'user' => $user];
            }
        }
        
        // Also check admin table for backward compatibility
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            // Verify admin password
            if ($password === $admin['password'] || password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['name'] = $admin['name'];
                $_SESSION['email'] = $admin['email'];
                $_SESSION['role'] = 'admin'; // Always admin role
                $_SESSION['logged_in'] = true;
                
                return ['success' => true, 'user' => $admin];
            }
        }
        
        // If login failed
        return ['success' => false, 'message' => 'Invalid email or password'];
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error. Please try again later.'];
    }
}

// User logout function
function logout() {
    // Clear all session variables
    $_SESSION = [];
    
    // If a session cookie is used, destroy it too
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: ../views/login.php');
    exit;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Get current user role
function getUserRole() {
    return $_SESSION['role'] ?? 'visitor';
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? 0;
}