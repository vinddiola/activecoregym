<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Member.php';
require_once __DIR__ . '/../../models/Coach.php';
require_once __DIR__ . '/../../models/Equipment.php';
require_once __DIR__ . '/../../models/Session.php';
require_once __DIR__ . '/../../models/Announcement.php';
require_once __DIR__ . '/../../models/WorkoutLog.php';
require_once __DIR__ . '/../../models/Workout.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Set page variables for header
$page_title = 'Admin Dashboard';
$current_page = 'dashboard';

$user = new User();
$member = new Member();
$coach = new Coach();
$equipment = new Equipment();
$session = new Session();
$announcement = new Announcement();
$workoutLog = new WorkoutLog();
$workout = new Workout();

// Get system statistics
$memberCount = $member->getActiveCount();
$coachCount = $coach->getCountByType('coach');
$equipmentCount = count($equipment->getAll());
$sessionStats = $session->getStats();
$workoutStats = $workoutLog->getOverallStats();
$announcementStats = $announcement->getStats();
$workoutPlanStats = $workout->getStats();

// Get today's sessions
$todaySessions = $session->getToday();

// Get recent activities
$recentMembers = $member->getAll(5, 0);
$recentSessions = $session->getAll(5, 0);
$recentAnnouncements = $announcement->getAll(5, 0);

// Get equipment maintenance needs
$maintenanceNeeded = $equipment->getMaintenanceNeeded();

// Include header
require_once __DIR__ . '/header.php';

// Helper functions
function getSessionStatusColor($status) {
    switch($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'info';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function getAnnouncementPriorityColor($priority) {
    switch($priority) {
        case 'high': return 'danger';
        case 'medium': return 'warning';
        case 'low': return 'info';
        default: return 'secondary';
    }
}
?>

        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="display-6 fw-bold mb-2">Admin Dashboard</h1>
                                <p class="lead mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>! Here's your system overview.</p>
                            </div>
                            <div class="text-end">
                                <i class="fas fa-shield-alt fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title"><?php echo $memberCount; ?></h3>
                                <p class="card-text">Active Members</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title"><?php echo $coachCount; ?></h3>
                                <p class="card-text">Active Coaches</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title"><?php echo $equipmentCount; ?></h3>
                                <p class="card-text">Total Equipment</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tools fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title"><?php echo $sessionStats['total_sessions']; ?></h3>
                                <p class="card-text">Total Sessions</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient">
                    <div class="card-body text-center">
                        <h3><?php echo $workoutStats['total_logs']; ?></h3>
                        <p class="card-text mb-0">Workout Logs</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient">
                    <div class="card-body text-center">
                        <h3><?php echo $workoutPlanStats['total_workouts']; ?></h3>
                        <p class="card-text mb-0">Workout Plans</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3><?php echo count($maintenanceNeeded); ?></h3>
                        <p class="mb-0">Maintenance Needed</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-dark text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $announcementStats['active']; ?></h3>
                        <p class="mb-0">Active Announcements</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $sessionStats['completed_sessions']; ?></h3>
                        <p class="mb-0">Completed Sessions</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Today's Sessions -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Sessions</h5>
                        <span class="badge bg-primary"><?php echo count($todaySessions); ?> sessions</span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($todaySessions)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Coach</th>
                                            <th>Member</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($todaySessions as $session): ?>
                                            <tr>
                                                <td><?php echo formatTime($session['session_time']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($session['coach_first_name'] . ' ' . $session['coach_last_name']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($session['member_first_name'] . ' ' . $session['member_last_name']); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo getSessionStatusColor($session['status']); ?>">
                                                        <?php echo ucfirst($session['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-day fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No sessions scheduled for today</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Members -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Recent Members</h5>
                        <a href="members.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentMembers)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentMembers as $member): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                                </h6>
                                                <small class="text-muted">
                                                    @<?php echo htmlspecialchars($member['username']); ?> • 
                                                    Joined: <?php echo formatDate($member['membership_date']); ?>
                                                </small>
                                            </div>
                                            <div>
                                                <span class="badge bg-<?php echo ($member['membership_status'] === 'active') ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($member['membership_status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No recent member registrations</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Equipment Status Overview -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-tools me-2"></i>Equipment Status
                        </h5>
                        <a href="equipment.php" class="btn btn-sm btn-outline-primary">View All Equipment</a>
                    </div>
                    <div class="card-body">
                        <?php 
                        // Get equipment statistics
                        $equipmentStats = $equipment->getStats();
                        $totalEquipment = $equipment->getCount();
                        $availableEquipment = 0;
                        $maintenanceEquipment = 0;
                        $outOfOrderEquipment = 0;
                        
                        foreach ($equipmentStats as $stat) {
                            if ($stat['status'] === 'available') $availableEquipment = $stat['count'];
                            if ($stat['status'] === 'maintenance') $maintenanceEquipment = $stat['count'];
                            if ($stat['status'] === 'out_of_order') $outOfOrderEquipment = $stat['count'];
                        }
                        ?>
                        
                        <div class="row mb-3">
                            <div class="col-3 text-center">
                                <div class="text-success">
                                    <h4><?php echo $availableEquipment; ?></h4>
                                    <small class="d-block">Available</small>
                                </div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="text-info">
                                    <h4><?php echo $maintenanceEquipment; ?></h4>
                                    <small class="d-block">Maintenance</small>
                                </div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="text-danger">
                                    <h4><?php echo $outOfOrderEquipment; ?></h4>
                                    <small class="d-block">Out of Order</small>
                                </div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="text-primary">
                                    <h4><?php echo $totalEquipment; ?></h4>
                                    <small class="d-block">Total</small>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($maintenanceNeeded)): ?>
                            <div class="alert alert-warning alert-sm">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Attention:</strong> <?php echo count($maintenanceNeeded); ?> equipment items need maintenance
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($maintenanceNeeded, 0, 3) as $item): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($item['category']); ?>
                                                </small>
                                            </div>
                                            <div>
                                                <span class="badge bg-<?php echo ($item['status'] === 'maintenance') ? 'info' : 'danger'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($maintenanceNeeded) > 3): ?>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">And <?php echo count($maintenanceNeeded) - 3; ?> more...</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p class="text-muted mb-0">All equipment is in good working condition</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Workout Activity Overview -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-running me-2"></i>Workout Activity
                        </h5>
                        <a href="workouts.php" class="btn btn-sm btn-outline-primary">View Workouts</a>
                    </div>
                    <div class="card-body">
                        <?php 
                        // Get overall workout statistics
                        $workoutStats = $workoutLog->getOverallStats();
                        ?>
                        
                        <div class="row mb-3">
                            <div class="col-3 text-center">
                                <div class="text-success">
                                    <h4><?php echo $workoutStats['total_logs'] ?? 0; ?></h4>
                                    <small class="d-block">Total Logs</small>
                                </div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="text-info">
                                    <h4><?php echo $workoutStats['active_members'] ?? 0; ?></h4>
                                    <small class="d-block">Active Members</small>
                                </div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="text-warning">
                                    <h4><?php echo $workoutPlanStats['total_workouts']; ?></h4>
                                    <small class="d-block">Plans</small>
                                </div>
                            </div>
                            <div class="col-3 text-center">
                                <div class="text-primary">
                                    <h4><?php echo $workoutStats['workout_days'] ?? 0; ?></h4>
                                    <small class="d-block">Workout Days</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info alert-sm">
                            <i class="fas fa-chart-line me-2"></i>
                            <strong>Activity:</strong> <?php echo $workoutStats['total_logs'] ?? 0; ?> total workout logs recorded
                        </div>
                        
                        <div class="text-center py-3">
                            <i class="fas fa-dumbbell fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Workout tracking system active</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Recent Announcements</h5>
                        <a href="announcements.php" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentAnnouncements)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentAnnouncements as $announcement): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($announcement['content'], 0, 80)) . '...'; ?>
                                                </small>
                                                <br><small class="text-muted">
                                                    By: <?php echo htmlspecialchars($announcement['created_by_username']); ?> • 
                                                    <?php echo formatDate($announcement['created_at']); ?>
                                                </small>
                                            </div>
                                            <div>
                                                <span class="badge bg-<?php echo getAnnouncementPriorityColor($announcement['priority']); ?>">
                                                    <?php echo ucfirst($announcement['priority']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-bullhorn fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No recent announcements</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="members.php?action=add" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>Add Member
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="coaches.php?action=add" class="btn btn-success w-100">
                                    <i class="fas fa-user-tie me-2"></i>Add Coach
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="workouts.php?action=add" class="btn btn-warning w-100">
                                    <i class="fas fa-running me-2"></i>Add Workout
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="announcements.php?action=add" class="btn btn-info w-100">
                                    <i class="fas fa-bullhorn me-2"></i>New Announcement
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End container -->

<?php
// Include footer
require_once __DIR__ . '/footer.php';
?>
