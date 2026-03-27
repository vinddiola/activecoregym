<?php
require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gymName = sanitizeInput($_POST['gym_name'] ?? 'ActiveCore Gym');
    $gymEmail = sanitizeInput($_POST['gym_email'] ?? 'info@activecoregym.com');
    $gymPhone = sanitizeInput($_POST['gym_phone'] ?? '+1234567890');
    $gymAddress = sanitizeInput($_POST['gym_address'] ?? '123 Gym Street, Fitness City');
    
    // Save settings (in a real app, this would go to database)
    $_SESSION['settings_updated'] = true;
    $_SESSION['success'] = 'Settings updated successfully!';
    
    header('Location: settings.php');
    exit();
}

// Get current settings (in a real app, this would come from database)
$currentSettings = [
    'gym_name' => 'ActiveCore Gym',
    'gym_email' => 'info@activecoregym.com',
    'gym_phone' => '+1234567890',
    'gym_address' => '123 Gym Street, Fitness City',
    'opening_hours' => '6:00 AM - 10:00 PM',
    'max_members' => '500',
    'membership_fee' => '$29.99/month'
];

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-cog me-2"></i>Settings</h2>
        <p class="text-muted">System configuration and preferences</p>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-4">
    <?php displayMessages(); ?>

    <div class="row">
        <!-- General Settings -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>General Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="gym_name" class="form-label">Gym Name</label>
                            <input type="text" class="form-control" id="gym_name" name="gym_name" 
                                   value="<?php echo htmlspecialchars($currentSettings['gym_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="gym_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="gym_email" name="gym_email" 
                                   value="<?php echo htmlspecialchars($currentSettings['gym_email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="gym_phone" class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="gym_phone" name="gym_phone" 
                                   value="<?php echo htmlspecialchars($currentSettings['gym_phone']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="gym_address" class="form-label">Address</label>
                            <textarea class="form-control" id="gym_address" name="gym_address" rows="3" required><?php echo htmlspecialchars($currentSettings['gym_address']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="opening_hours" class="form-label">Opening Hours</label>
                                <input type="text" class="form-control" id="opening_hours" name="opening_hours" 
                                       value="<?php echo htmlspecialchars($currentSettings['opening_hours']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_members" class="form-label">Max Members</label>
                                <input type="number" class="form-control" id="max_members" name="max_members" 
                                       value="<?php echo htmlspecialchars($currentSettings['max_members']); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="membership_fee" class="form-label">Monthly Membership Fee</label>
                            <input type="text" class="form-control" id="membership_fee" name="membership_fee" 
                                   value="<?php echo htmlspecialchars($currentSettings['membership_fee']); ?>">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <h6>ActiveCore Gym Management System</h6>
                    <p class="text-muted">Version 1.0.0</p>
                    
                    <hr>
                    
                    <h6>System Status</h6>
                    <div class="mb-2">
                        <span class="badge bg-success">Database Connected</span>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-success">All Systems Operational</span>
                    </div>
                    
                    <hr>
                    
                    <h6>Last Updated</h6>
                    <p class="text-muted"><?php echo date('F j, Y, g:i A'); ?></p>
                    
                    <hr>
                    
                    <h6>Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-info btn-sm" onclick="backupDatabase()">
                            <i class="fas fa-download me-1"></i>Backup Database
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="clearCache()">
                            <i class="fas fa-trash me-1"></i>Clear Cache
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="viewLogs()">
                            <i class="fas fa-file-alt me-1"></i>View Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Password Policy</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="require_strong_password" checked>
                                <label class="form-check-label" for="require_strong_password">
                                    Require strong passwords (8+ chars, mixed case, numbers)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="password_expiry" checked>
                                <label class="form-check-label" for="password_expiry">
                                    Password expiry reminders (90 days)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Session Settings</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="session_timeout" checked>
                                <label class="form-check-label" for="session_timeout">
                                    Auto-logout after 1 hour of inactivity
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="login_attempts" checked>
                                <label class="form-check-label" for="login_attempts">
                                    Lock account after 5 failed login attempts
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Security Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        function clearCache() {
            if (confirm('Clear all system cache? This may temporarily slow down the system.')) {
                alert('Cache cleared successfully!');
            }
        }

        function viewLogs() {
            alert('Log viewer functionality coming soon!');
        }
    </script>
</body>
</html>
