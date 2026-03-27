<?php
/**
 * Admin Header - Common header for all admin pages
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - ActiveCore Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>css/style.css" rel="stylesheet">
    <style>
        /* CRITICAL: Override ALL Bootstrap text styles for admin pages */
        .card-header h5,
        .card-body,
        .form-label,
        .form-control,
        .form-select,
        textarea,
        .btn,
        .alert,
        .input-group-text,
        .text-gold,
        .text-light-gold,
        h2, h3, h4, h5, h6,
        p,
        .nav-link,
        .navbar-brand {
            color: #FFFFFF !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important;
            font-weight: 600 !important;
        }
        
        /* Specific form element fixes */
        .card {
            background: rgba(0,0,0,0.9) !important;
            border: 2px solid #FFD700 !important;
        }
        
        .form-control, .form-select, textarea {
            border: 2px solid #FFD700 !important;
            color: #FFFFFF !important;
            background: rgba(255,255,255,0.1) !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%) !important;
            color: #FFFFFF !important;
            border: none;
        }
        
        .btn-outline-secondary, .btn-outline-warning {
            color: #FFFFFF !important;
            border-color: #FFD700 !important;
        }
    </style>
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
            background-color: var(--black-bg);
            color: var(--light-gold);
        }
        
        .navbar-dark {
            background-color: var(--dark-gray) !important;
            border-bottom: 2px solid var(--primary-gold);
        }
        
        .navbar-brand {
            color: var(--primary-gold) !important;
            font-weight: bold;
        }
        
        .nav-link {
            color: var(--light-gold) !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-gold) !important;
            background-color: rgba(255, 215, 0, 0.1);
        }
        
        .nav-link.active {
            color: var(--primary-gold) !important;
            background-color: rgba(255, 215, 0, 0.2);
            border-bottom: 2px solid var(--primary-gold);
        }
        
        .card {
            background-color: var(--gray-bg);
            border: 1px solid var(--dark-gold);
            color: var(--light-gold);
        }
        
        .card-header {
            background-color: var(--dark-gray);
            border-bottom: 1px solid var(--dark-gold);
            color: var(--primary-gold);
        }
        
        .btn-primary {
            background-color: var(--primary-gold);
            border-color: var(--dark-gold);
            color: var(--black-bg);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-gold);
            border-color: var(--primary-gold);
            color: var(--light-gold);
        }
        
        .btn-outline-primary {
            border-color: var(--primary-gold);
            color: var(--primary-gold);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-gold);
            color: var(--black-bg);
        }
        
        .table {
            color: var(--light-gold);
        }
        
        .table thead th {
            background-color: var(--dark-gray);
            color: var(--primary-gold);
            border-bottom: 1px solid var(--dark-gold);
        }
        
        .table tbody tr:hover {
            background-color: rgba(255, 215, 0, 0.1);
        }
        
        .text-primary {
            color: var(--primary-gold) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-gold) !important;
            color: var(--black-bg) !important;
        }
        
        .bg-gradient {
            background: linear-gradient(135deg, var(--dark-gray) 0%, var(--gray-bg) 100%) !important;
        }
        
        .alert {
            border-left: 4px solid var(--primary-gold);
        }
        
        .alert-success {
            background-color: rgba(184, 134, 11, 0.2);
            border-color: var(--dark-gold);
            color: var(--light-gold);
        }
        
        .alert-warning {
            background-color: rgba(255, 215, 0, 0.2);
            border-color: var(--primary-gold);
            color: var(--light-gold);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            border-color: #dc3545;
            color: var(--light-gold);
        }
        
        .form-control {
            background-color: var(--gray-bg);
            border: 1px solid var(--dark-gold);
            color: var(--light-gold);
        }
        
        .form-control:focus {
            background-color: var(--gray-bg);
            border-color: var(--primary-gold);
            color: var(--light-gold);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        
        .form-label {
            color: var(--primary-gold);
        }
        
        .modal-content {
            background-color: var(--gray-bg);
            border: 1px solid var(--primary-gold);
        }
        
        .modal-header {
            background-color: var(--dark-gray);
            border-bottom: 1px solid var(--primary-gold);
        }
        
        .modal-footer {
            background-color: var(--dark-gray);
            border-top: 1px solid var(--primary-gold);
        }
        
        .dropdown-menu {
            background-color: var(--gray-bg);
            border: 1px solid var(--primary-gold);
        }
        
        .dropdown-item {
            color: var(--light-gold);
        }
        
        .dropdown-item:hover {
            background-color: var(--primary-gold);
            color: var(--black-bg);
        }
        
        .badge {
            padding: 0.35em 0.65em;
        }
        
        .bg-success {
            background-color: var(--dark-gold) !important;
            color: var(--light-gold) !important;
        }
        
        .bg-warning {
            background-color: var(--primary-gold) !important;
            color: var(--black-bg) !important;
        }
        
        .bg-danger {
            background-color: #dc3545 !important;
            color: var(--light-gold) !important;
        }
        
        .bg-info {
            background-color: #17a2b8 !important;
            color: var(--light-gold) !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-dumbbell"></i> <strong>ActiveCore</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'members') ? 'active' : ''; ?>" href="members.php">
                            <i class="fas fa-users me-2"></i>Members
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'coaches') ? 'active' : ''; ?>" href="coaches.php">
                            <i class="fas fa-user-tie me-2"></i>Coaches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'equipment') ? 'active' : ''; ?>" href="equipment.php">
                            <i class="fas fa-tools me-2"></i>Equipment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'workouts') ? 'active' : ''; ?>" href="workouts.php">
                            <i class="fas fa-running me-2"></i>Workouts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'announcements') ? 'active' : ''; ?>" href="announcements.php">
                            <i class="fas fa-bullhorn me-2"></i>Announcements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'settings') ? 'active' : ''; ?>" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php if (function_exists('displayMessages')): ?>
            <?php displayMessages(); ?>
        <?php endif; ?>
