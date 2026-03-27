<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Announcement.php';

// Check if user is logged in and is an admin
checkAccess('admin');

$announcement = new Announcement();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'archive_expired') {
        $announcement->deactivateExpired();
        $_SESSION['success'] = 'Archived expired announcements successfully';
        
        header('Location: announcements.php');
        exit;
    }
}

// If not POST request, redirect back
header('Location: announcements.php');
exit;
?>
