<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Member.php';
require_once __DIR__ . '/../../models/WorkoutLog.php';
require_once __DIR__ . '/../../models/Session.php';
require_once __DIR__ . '/../../models/Announcement.php';
require_once __DIR__ . '/../../models/Exercise.php';

// Check if user is logged in and is a member
checkAccess('member');

$authController = new AuthController();
$member = new Member();
$workoutLog = new WorkoutLog();
$session = new Session();
$announcement = new Announcement();
$exercise = new Exercise();

// Get member data
$memberData = $authController->getProfile();
$memberId = $_SESSION['member_id'];

// Get member statistics
$stats = $workoutLog->getMemberStats($memberId);
$recentWorkouts = $workoutLog->getRecentWorkouts($memberId, 5);
$upcomingSessions = $session->getUpcomingSessions($memberId);
$announcements = $announcement->getActive(3);
$recommendedExercises = $exercise->getRecommendations($memberId, 4);
$personalBests = $workoutLog->getPersonalBests($memberId, 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - ActiveCore Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>css/theme.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="workouts.php">
                            <i class="fas fa-running me-2"></i>Workouts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sessions.php">
                            <i class="fas fa-calendar me-2"></i>Sessions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="progress.php">
                            <i class="fas fa-chart-line me-2"></i>Progress
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="memberDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($memberData['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>My Profile
                            </a></li>
                            <li><a class="dropdown-item" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
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
                <div class="card bg-gradient">
                    <div class="card-body text-center">
                        <h1 class="display-6 fw-bold gold-gradient">Welcome Back, <?php echo htmlspecialchars($memberData['first_name']); ?>! �</h1>
                        <p class="lead">Your Fitness Journey Continues</p>
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
                                <h4 class="card-title"><?php echo $stats['total_workouts'] ?? 0; ?></h4>
                                <p class="card-text">Total Workouts</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-dumbbell fa-2x"></i>
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
                                <h4 class="card-title"><?php echo $stats['workout_days'] ?? 0; ?></h4>
                                <p class="card-text">Workout Days</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-check fa-2x"></i>
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
                                <h4 class="card-title"><?php echo $stats['max_weight'] ?? 0; ?> kg</h4>
                                <p class="card-text">Max Weight</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-trophy fa-2x"></i>
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
                                <h4 class="card-title"><?php echo count($upcomingSessions); ?></h4>
                                <p class="card-text">Upcoming Sessions</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Workouts -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Workouts</h5>
                        <a href="workout_tracker.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentWorkouts)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentWorkouts as $workout): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($workout['exercise_name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo $workout['sets']; ?> sets × <?php echo $workout['reps']; ?> reps
                                                    <?php if ($workout['weight']): ?>
                                                        @ <?php echo $workout['weight']; ?> kg
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted"><?php echo formatDate($workout['workout_date']); ?></small>
                                                <div>
                                                    <span class="badge bg-<?php echo ($workout['difficulty_level'] === 'beginner') ? 'success' : (($workout['difficulty_level'] === 'intermediate') ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($workout['difficulty_level']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-dumbbell fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No workouts recorded yet.</p>
                                <a href="workout_tracker.php" class="btn btn-primary">Start Your First Workout</a>
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
                        <a href="hire_coach.php" class="btn btn-sm btn-outline-primary">Book Session</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($upcomingSessions)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($upcomingSessions as $session): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($session['coach_first_name'] . ' ' . $session['coach_last_name']); ?>
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
                                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No upcoming sessions scheduled.</p>
                                <a href="hire_coach.php" class="btn btn-primary">Book a Coach</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Personal Bests -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Personal Bests</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($personalBests)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($personalBests as $best): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($best['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($best['muscle_group']); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <strong><?php echo $best['max_weight']; ?> kg</strong>
                                                <br><small class="text-muted"><?php echo formatDate($best['achieved_date']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                                <p class="text-muted small">Start working out to set your personal bests!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recommended Exercises -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Recommended Exercises</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recommendedExercises)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recommendedExercises as $exercise): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($exercise['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($exercise['muscle_group'] ?? 'General'); ?></small>
                                            </div>
                                            <span class="badge bg-<?php echo ($exercise['difficulty_level'] === 'beginner') ? 'success' : (($exercise['difficulty_level'] === 'intermediate') ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($exercise['difficulty_level']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-dumbbell fa-2x text-muted mb-2"></i>
                                <p class="text-muted small">Try different exercises to get recommendations!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Latest Announcements</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($announcements)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="list-group-item">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($announcement['content'], 0, 80)) . '...'; ?></small>
                                            <br><small class="text-muted"><?php echo formatDate($announcement['created_at']); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted small">No new announcements</p>
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
                        <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="workout_tracker.php" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Log Workout
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="exercises.php" class="btn btn-info w-100">
                                    <i class="fas fa-list me-2"></i>Browse Exercises
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="hire_coach.php" class="btn btn-warning w-100">
                                    <i class="fas fa-user-tie me-2"></i>Book Coach
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="equipment.php" class="btn btn-success w-100">
                                    <i class="fas fa-tools me-2"></i>View Equipment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/script.js"></script>
</body>
</html>
