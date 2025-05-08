<?php
require_once '../config/auth.php';

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect to blog frontend
    header('Location: blog_frontend.php');
    exit;
}

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Attempt login
        $result = login($email, $password);
        
        if ($result['success']) {
            // Redirect based on role
            $redirectTo = ($_SESSION['role'] === 'admin') ? 'blog_backend.php' : 'blog_frontend.php';
            header("Location: $redirectTo");
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WorldVenture</title>
    <link rel="stylesheet" href="../../main_front/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/validation.js"></script>
    <style>
        .login-container {
            max-width: 450px;
            margin: 3rem auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header img {
            width: 150px;
            margin-bottom: 1rem;
        }
        
        .login-header h1 {
            color: #0b2447;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #64748b;
            font-size: 0.95rem;
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-group {
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #334155;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            padding-left: 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            border-color: #3e92cc;
            box-shadow: 0 0 0 2px rgba(62, 146, 204, 0.2);
            outline: none;
        }
        
        .form-group i {
            position: absolute;
            left: 0.75rem;
            top: 2.3rem;
            color: #64748b;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #3e92cc, #0a4c8c);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, #0073e6, #0088b3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(62, 146, 204, 0.4);
        }
        
        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            color: #64748b;
        }
        
        .login-footer a {
            color: #3e92cc;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .login-footer a:hover {
            color: #0a4c8c;
            text-decoration: underline;
        }
        
        .error-message {
            background: #fee2e2;
            color: #ef4444;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .guest-login {
            display: block;
            margin-top: 1.5rem;
            background: #f1f5f9;
            color: #64748b;
            text-align: center;
            padding: 0.75rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .guest-login:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        
        .validation-error {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }
        
        body {
            background: url('../../main_front/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: rgba(255, 255, 255, 0.5);  /* Semi-transparent white overlay */
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background-color: rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>
    
    <div class="login-container">
        <div class="login-header">
            <img src="../../main_front/logo.png" alt="WorldVenture Logo">
            <h1>Welcome Back</h1>
            <p>Sign in to continue your adventure</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <form class="login-form" id="loginForm" method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Your email address" required>
                <div id="emailError" class="validation-error"></div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Your password" required>
                <div id="passwordError" class="validation-error"></div>
            </div>
            <button type="submit" id="loginButton" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Log In
            </button>
        </form>
        
        <div class="login-footer">
            <p>Don't have an account? <a href="#">Sign up</a></p>
        </div>
        
        <a href="blog_frontend.php?guest=true" class="guest-login">
            <i class="fas fa-user-secret"></i> Continue as Guest
        </a>
    </div>
</body>
</html>