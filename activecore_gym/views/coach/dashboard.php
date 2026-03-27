<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Coach.php';
require_once __DIR__ . '/../../models/Session.php';
require_once __DIR__ . '/../../models/Member.php';

// Check if user is logged in and is a coach
checkAccess('coach');

$authController = new AuthController();
$coach = new Coach();
$session = new Session();
$member = new Member();

// Get coach data
$coachData = $authController->getProfile();
$coachId = $_SESSION['coach_id'];

// Get coach statistics
$stats = $coach->getStats($coachId);
$upcomingSessions = $coach->getUpcomingSessions($coachId);
$todaySchedule = $coach->getDailySchedule($coachId, date('Y-m-d'));
$recentSessions = $coach->getSessionHistory($coachId, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard - ActiveCore Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>css/style.css" rel="stylesheet">
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sessions.php">Sessions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule.php">Schedule</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="members.php">Members</a>
                    </li>
                </ul>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php displayMessages(); ?>

        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient text-white">
                    <div class="card-body">
                        <h1 class="display-6 fw-bold">Welcome back, <?php echo htmlspecialchars($coachData['first_name']); ?>! 👋</h1>
                        <p class="lead">Ready to help your members achieve their fitness goals?</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['total_sessions']; ?></h4>
                                <p class="card-text">Total Sessions</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['completed_sessions']; ?></h4>
                                <p class="card-text">Completed</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['pending_sessions']; ?></h4>
                                <p class="card-text">Pending</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['unique_members']; ?></h4>
                                <p class="card-text">Unique Members</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Today's Schedule -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Schedule</h5>
                        <span class="badge bg-primary"><?php echo date('l, F j'); ?></span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($todaySchedule)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($todaySchedule as $session): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php echo formatTime($session['session_time']); ?> - 
                                                    <?php echo htmlspecialchars($session['member_first_name'] . ' ' . $session['member_last_name']); ?>
                                                </h6>
                                                <small class="text-muted">Session ID: #<?php echo $session['session_id']; ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo ($session['status'] === 'confirmed') ? 'success' : (($session['status'] === 'pending') ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($session['status']); ?>
                                                </span>
                                                <div class="mt-2">
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($session['status'] === 'pending'): ?>
                                                            <button class="btn btn-sm btn-success" onclick="confirmSession(<?php echo $session['session_id']; ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($session['status'] === 'confirmed'): ?>
                                                            <button class="btn btn-sm btn-primary" onclick="completeSession(<?php echo $session['session_id']; ?>)">
                                                                <i class="fas fa-check-double"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelSession(<?php echo $session['session_id']; ?>)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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

            <!-- Upcoming Sessions -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming Sessions</h5>
                        <a href="sessions.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($upcomingSessions)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($upcomingSessions as $session): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($session['member_first_name'] . ' ' . $session['member_last_name']); ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?php echo formatDate($session['session_date']); ?> at <?php echo formatTime($session['session_time']); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo ($session['status'] === 'confirmed') ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($session['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-alt fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No upcoming sessions</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Sessions -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Sessions</h5>
                        <a href="sessions.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentSessions)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Member</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentSessions as $session): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($session['member_first_name'] . ' ' . $session['member_last_name']); ?>
                                                </td>
                                                <td><?php echo formatDate($session['session_date']); ?></td>
                                                <td><?php echo formatTime($session['session_time']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($session['status'] === 'completed') ? 'success' : (($session['status'] === 'confirmed') ? 'info' : (($session['status'] === 'pending') ? 'warning' : 'danger')); ?>">
                                                        <?php echo ucfirst($session['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($session['status'] === 'pending'): ?>
                                                            <button class="btn btn-sm btn-success" onclick="confirmSession(<?php echo $session['session_id']; ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($session['status'] === 'confirmed'): ?>
                                                            <button class="btn btn-sm btn-primary" onclick="completeSession(<?php echo $session['session_id']; ?>)">
                                                                <i class="fas fa-check-double"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($session['status'] === 'completed'): ?>
                                                            <button class="btn btn-sm btn-outline-warning" onclick="viewSessionNotes(<?php echo $session['session_id']; ?>)">
                                                                <i class="fas fa-sticky-note"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelSession(<?php echo $session['session_id']; ?>)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No session history yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Coach Profile Summary -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Your Profile</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="fas fa-user-circle fa-4x text-primary"></i>
                        </div>
                        <h6 class="text-center"><?php echo htmlspecialchars($coachData['first_name'] . ' ' . $coachData['last_name']); ?></h6>
                        <p class="text-center text-muted"><?php echo htmlspecialchars($coachData['specialization'] ?? 'General Fitness'); ?></p>
                        
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <strong><?php echo $coachData['experience_years']; ?></strong>
                                <br><small class="text-muted">Years Experience</small>
                            </div>
                            <div class="col-6">
                                <strong><?php echo number_format($coachData['rating'], 1); ?> ⭐</strong>
                                <br><small class="text-muted">Rating</small>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="profile.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-edit me-2"></i>Edit Profile
                            </a>
                            <a href="schedule.php" class="btn btn-outline-info">
                                <i class="fas fa-calendar me-2"></i>View Schedule
                            </a>
                        </div>
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
                                <a href="sessions.php" class="btn btn-primary w-100">
                                    <i class="fas fa-calendar-alt me-2"></i>Manage Sessions
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="schedule.php" class="btn btn-info w-100">
                                    <i class="fas fa-calendar me-2"></i>View Schedule
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="members.php" class="btn btn-success w-100">
                                    <i class="fas fa-users me-2"></i>View Members
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="profile.php" class="btn btn-warning w-100">
                                    <i class="fas fa-user-edit me-2"></i>Update Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmSession(sessionId) {
            if (confirm('Are you sure you want to confirm this session?')) {
                fetch(`update_session.php?id=${sessionId}&action=confirm`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to confirm session');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error confirming session');
                });
            }
        }

        function completeSession(sessionId) {
            if (confirm('Are you sure you want to mark this session as completed?')) {
                fetch(`update_session.php?id=${sessionId}&action=complete`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to complete session');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error completing session');
                });
            }
        }

        function cancelSession(sessionId) {
            if (confirm('Are you sure you want to cancel this session?')) {
                fetch(`update_session.php?id=${sessionId}&action=cancel`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to cancel session');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error cancelling session');
                });
            }
        }

        function viewSessionNotes(sessionId) {
            // Implement session notes view
            alert('Session notes view coming soon!');
        }
    </script>
</body>
</html>
