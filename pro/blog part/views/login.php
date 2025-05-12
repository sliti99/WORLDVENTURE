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
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-group input:focus {
            border-color: #3e92cc;
            box-shadow: 0 0 0 3px rgba(62, 146, 204, 0.15);
            outline: none;
        }
        
        .form-group i {
            position: absolute;
            left: 1rem;
            top: 2.4rem;
            color: #64748b;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #3e92cc, #0a4c8c);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 0.5rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(10, 76, 140, 0.3);
        }
        
        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: #3e92cc;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .login-footer a:hover {
            color: #0a4c8c;
            text-decoration: underline;
        }
        
        .error-message {
            background: #fee2e2;
            color: #ef4444;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .guest-login {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .guest-login:hover {
            color: #334155;
            text-decoration: underline;
        }
        
        .validation-error {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.3rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="background-image"></div>
    <div class="overlay"></div>
    
    <div class="login-container">
        <div class="login-header">
            <img src="../../main_front/logo.png" alt="WorldVenture Logo">
            <h1>Welcome Back!</h1>
            <p>Login to access your WorldVenture account</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="email">Email Address</label>
                <i class="fas fa-envelope" style="top: 39px; left: 15px; color: #64748b;"></i>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                <div id="emailError" class="validation-error"></div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock" style="top: 39px; left: 15px; color: #64748b;"></i>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <div id="passwordError" class="validation-error"></div>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="login-footer">
            <p>Don't have an account? <a href="blog_frontend.php?guest=true">Continue as a guest</a></p>
        </div>
        
        <a href="blog_frontend.php?guest=true" class="guest-login">
            <i class="fas fa-user-secret"></i> Browse as Guest
        </a>
    </div>
    
    <script>
        function validateForm() {
            let isValid = true;
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');
            
            // Reset error messages
            emailError.style.display = 'none';
            passwordError.style.display = 'none';
            
            // Validate email
            if (email === '') {
                emailError.textContent = 'Email address is required';
                emailError.style.display = 'block';
                isValid = false;
            } else if (!isValidEmail(email)) {
                emailError.textContent = 'Please enter a valid email address';
                emailError.style.display = 'block';
                isValid = false;
            }
            
            // Validate password
            if (password === '') {
                passwordError.textContent = 'Password is required';
                passwordError.style.display = 'block';
                isValid = false;
            } else if (password.length < 4) {
                passwordError.textContent = 'Password must be at least 4 characters';
                passwordError.style.display = 'block';
                isValid = false;
            }
            
            return isValid;
        }
        
        function isValidEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email.toLowerCase());
        }
    </script>
</body>
</html>