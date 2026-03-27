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

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'username' => sanitizeInput($_POST['username'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'user_type' => sanitizeInput($_POST['user_type'] ?? 'member')
    ];

    $profileData = [
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'specialization' => sanitizeInput($_POST['specialization'] ?? ''),
        'experience_years' => intval($_POST['experience_years'] ?? 0)
    ];

    $result = $authController->register($userData, $profileData);
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: ' . BASE_URL . 'views/auth/login.php');
        exit();
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
    <title>Register - ActiveCore Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .register-container {
            background: rgba(0, 0, 0, 0.9);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .brand-name {
            font-size: 2.5rem;
            font-weight: bold;
            color: #FFD700;
            margin-bottom: 10px;
            text-align: center;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #FFD700;
            padding: 12px 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #FFD700;
            box-shadow: 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            color: white;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .user-type-card {
            border: 2px solid #FFD700;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-type-card:hover, .user-type-card.active {
            border-color: #FFD700;
            background: rgba(0, 0, 0, 0.1);
        }
        .coach-fields {
            display: none;
        }
        .coach-fields.show {
            display: block;
        }
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s;
        }
        .form-label {
            font-weight: 600;
            color: #FFD700;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="text-center mb-4">
                <div class="brand-name" style="color: #FFD700 !important; text-shadow: 3px 3px 6px rgba(0,0,0,1) !important; font-weight: 700 !important;">
                    <i class="fas fa-dumbbell" style="color: #FFD700 !important;"></i> ActiveCore
                </div>
                <p class="text-muted" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">Join our fitness community today</p>
            </div>

            <?php displayMessages(); ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">
                    <i class="fas fa-exclamation-triangle me-2" style="color: #FFFFFF !important;"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="color: #FFFFFF !important;"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3" style="color: #FFD700 !important; text-shadow: 3px 3px 6px rgba(0,0,0,1) !important; font-weight: 700 !important;"><i class="fas fa-user me-2" style="color: #FFD700 !important;"></i>Account Information</h5>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required
                                   style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required
                                   style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                            <div class="password-strength" id="passwordStrength" style="background: rgba(255,255,255,0.2) !important;"></div>
                            <small class="text-muted" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">Minimum 6 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                   style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Account Type *</label>
                            <div class="user-type-card" data-type="member" style="border: 2px solid #FFD700 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_type" id="member_type" 
                                           value="member" <?php echo (($_POST['user_type'] ?? 'member') === 'member' ? 'checked' : ''); ?> required>
                                    <label class="form-check-label" for="member_type" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">
                                        <strong style="color: #FFD700 !important;">Gym Member</strong>
                                        <p class="text-muted small mb-0" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">Join as a gym member to track workouts and book sessions</p>
                                    </label>
                                </div>
                            </div>
                            <div class="user-type-card" data-type="coach" style="border: 2px solid #FFD700 !important;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_type" id="coach_type" 
                                           value="coach" <?php echo (($_POST['user_type'] ?? '') === 'coach' ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="coach_type" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">
                                        <strong style="color: #FFD700 !important;">Fitness Coach</strong>
                                        <p class="text-muted small mb-0" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">Join as a coach to train members and manage sessions</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-3" style="color: #FFD700 !important; text-shadow: 3px 3px 6px rgba(0,0,0,1) !important; font-weight: 700 !important;"><i class="fas fa-id-card me-2" style="color: #FFD700 !important;"></i>Personal Information</h5>
                        
                        <div class="mb-3">
                            <label for="first_name" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required
                                   style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required
                                   style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                   style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                        </div>

                        <div class="coach-fields" id="coachFields">
                            <div class="mb-3">
                                <label for="specialization" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Specialization</label>
                                <select class="form-select" id="specialization" name="specialization"
                                        style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                                    <option value="">Select specialization</option>
                                    <option value="Strength Training">Strength Training</option>
                                    <option value="Cardio">Cardio</option>
                                    <option value="Yoga">Yoga</option>
                                    <option value="CrossFit">CrossFit</option>
                                    <option value="Personal Training">Personal Training</option>
                                    <option value="Nutrition">Nutrition</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="experience_years" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Years of Experience</label>
                                <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                       value="<?php echo htmlspecialchars($_POST['experience_years'] ?? '0'); ?>" min="0" max="50"
                                       style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-register btn-lg px-5" style="background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%) !important; color: #FFFFFF !important; border: none; font-weight: 700 !important;">
                        <i class="fas fa-user-plus me-2" style="color: #FFFFFF !important;"></i>Create Account
                    </button>
                </div>

                <div class="text-center mt-3">
                    <p class="mb-0" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">Already have an account? 
                        <a href="<?php echo BASE_URL; ?>views/auth/login.php" class="text-decoration-none" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">
                            <strong style="color: #FFD700 !important;">Sign in here</strong>
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User type selection
        document.querySelectorAll('.user-type-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.user-type-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                this.querySelector('input[type="radio"]').checked = true;
                
                // Show/hide coach fields
                const coachFields = document.getElementById('coachFields');
                if (this.dataset.type === 'coach') {
                    coachFields.classList.add('show');
                } else {
                    coachFields.classList.remove('show');
                }
            });
        });

        // Set initial active state
        document.querySelector('input[name="user_type"]:checked').closest('.user-type-card').classList.add('active');
        if (document.querySelector('input[name="user_type"]:checked').value === 'coach') {
            document.getElementById('coachFields').classList.add('show');
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.style.backgroundColor = '#e0e0e0';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength += 25;
            if (password.length >= 10) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            
            strengthBar.style.width = strength + '%';
            
            if (strength < 50) {
                strengthBar.style.backgroundColor = '#dc3545';
            } else if (strength < 75) {
                strengthBar.style.backgroundColor = '#ffc107';
            } else {
                strengthBar.style.backgroundColor = '#28a745';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>
