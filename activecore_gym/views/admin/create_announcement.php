<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Announcement.php';

// Check if user is logged in and is an admin
checkAccess('admin');

$announcement = new Announcement();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_announcement') {
        $announcement->title = sanitizeInput($_POST['title'] ?? '');
        $announcement->content = sanitizeInput($_POST['content'] ?? '');
        $announcement->announcement_type = sanitizeInput($_POST['announcement_type'] ?? 'general');
        $announcement->priority = sanitizeInput($_POST['priority'] ?? 'medium');
        $announcement->expires_at = sanitizeInput($_POST['expires_at'] ?? '');
        $announcement->is_active = true;
        $announcement->created_by = $_SESSION['user_id'];
        
        if ($announcement->create()) {
            $_SESSION['success'] = 'Announcement created successfully';
        } else {
            $_SESSION['error'] = 'Failed to create announcement';
        }
        
        header('Location: announcements.php');
        exit;
    }
}

// If not POST request, redirect back
header('Location: announcements.php');
exit;
?>
