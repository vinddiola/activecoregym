<?php
/**
 * Admin Index - Redirect to Dashboard
 * This file ensures backward compatibility and redirects to the main admin dashboard
 */

require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Redirect to the main admin dashboard
header('Location: dashboard.php');
exit();
?>
