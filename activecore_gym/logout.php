<?php
/**
 * Logout Script
 * ActiveCore Gym Management System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/AuthController.php';

$authController = new AuthController();

// Logout user
$result = $authController->logout();

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = 'Failed to logout properly';
}

// Redirect to login page
header('Location: ' . BASE_URL . 'views/auth/login.php');
exit();
?>
