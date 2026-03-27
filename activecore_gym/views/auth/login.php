<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectBasedOnUserType();
}

$authController = new AuthController();
$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = $authController->login($username, $password);
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        redirectBasedOnUserType();
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ActiveCore Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gold: #FFD700;
            --dark-gold: #B8860B;
            --light-gold: #FFF8DC;
            --black-bg: #000000;
            --dark-gray: #1a1a1a;
            --gray-bg: #2d2d2d;
        }
        
        body {
            background: linear-gradient(135deg, var(--black-bg) 0%, var(--dark-gray) 100%);
            min-height: 100vh;
            color: var(--light-gold);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(45, 45, 45, 0.95);
            border: 2px solid var(--primary-gold);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(255, 215, 0, 0.3);
            backdrop-filter: blur(10px);
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--dark-gray) 0%, var(--gray-bg) 100%);
            border-radius: 15px 15px 0 0;
            border-bottom: 2px solid var(--primary-gold);
            text-align: center;
            padding: 30px 20px;
        }
        
        .login-header h1 {
            color: var(--primary-gold);
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .login-header p {
            color: var(--dark-gold);
            margin: 0;
            font-size: 1.1rem;
        }
        
        .form-control {
            background-color: var(--gray-bg);
            border: 1px solid var(--dark-gold);
            color: var(--light-gold);
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
        }
        
        .form-control::placeholder {
            color: var(--dark-gold);
            opacity: 0.8;
        }
        
        .form-control:focus::placeholder {
            color: transparent;
        }
        
        .btn-outline-light {
            border-color: var(--primary-gold) !important;
            color: var(--primary-gold) !important;
            background-color: transparent !important;
        }
        
        .btn-outline-light:hover {
            background-color: var(--primary-gold) !important;
            color: var(--black-bg) !important;
            border-color: var(--primary-gold) !important;
        }
        
        .form-control:focus {
            background-color: var(--gray-bg);
            border-color: var(--primary-gold);
            color: var(--light-gold);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        
        .form-label {
            color: var(--primary-gold);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-gold) 0%, var(--dark-gold) 100%);
            border: 2px solid var(--primary-gold);
            color: var(--black-bg);
            font-weight: bold;
            font-size: 1.1rem;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, var(--dark-gold) 0%, var(--primary-gold) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.4);
        }
        
        .alert {
            border-left: 4px solid var(--primary-gold);
            background-color: rgba(45, 45, 45, 0.9);
            border: 1px solid var(--dark-gold);
            color: var(--light-gold);
            border-radius: 8px;
        }
        
        .text-gold {
            color: var(--primary-gold);
        }
        
        .text-muted {
            color: var(--dark-gold) !important;
        }
        
        .text-muted:hover {
            color: var(--primary-gold) !important;
        }
        
        .form-check-input {
            background-color: var(--gray-bg);
            border: 1px solid var(--dark-gold);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-gold);
            border-color: var(--primary-gold);
        }
        
        .form-check-input:focus {
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.25);
        }
        
        .form-check-label {
            color: var(--light-gold);
            font-weight: 500;
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
            margin: 25px 0;
        }
        
        .feature-text {
            color: var(--primary-gold);
            font-size: 0.9rem;
            text-align: center;
            margin-top: 15px;
        }
        
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--dark-gray);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--dark-gold);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-gold);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-dumbbell"></i> ActiveCore</h1>
                <p>Gym Management System</p>
            </div>
            <div class="card-body p-4">
                <?php displayMessages(); ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: var(--gray-bg); border: 1px solid var(--dark-gold); color: var(--primary-gold);">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Enter your username" required
                                   style="border-left: none;" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background-color: var(--gray-bg); border: 1px solid var(--dark-gold); color: var(--primary-gold);">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required
                                   style="border-left: none;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login btn-lg w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>

                    <div class="text-center">
                        <a href="#" class="text-muted">Forgot password?</a>
                    </div>
                </form>

                <div class="text-center mt-4">
                        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-light me-3">
                            <i class="fas fa-arrow-left me-1"></i>Back to Home
                        </a>
                        <p class="mb-0 mt-3">Don't have an account? 
                            <a href="<?php echo BASE_URL; ?>views/auth/register.php" class="text-decoration-none">
                                <strong>Sign up here</strong>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
