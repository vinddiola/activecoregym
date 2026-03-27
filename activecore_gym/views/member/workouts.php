<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/Member.php';
require_once __DIR__ . '/../../models/Workout.php';
require_once __DIR__ . '/../../models/WorkoutLog.php';

// Check if user is logged in and is a member
checkAccess('member');

$authController = new AuthController();
$member = new Member();
$workout = new Workout();
$workoutLog = new WorkoutLog();

// Get member data
$memberData = $authController->getProfile();
$memberId = $_SESSION['member_id'];

// Determine member's fitness level based on workout history
$memberStats = $workoutLog->getMemberStats($memberId);
$fitnessLevel = 'beginner'; // Default to beginner

// Simple logic to determine fitness level based on workout frequency and experience
if ($memberStats['total_workouts'] > 20) {
    $fitnessLevel = 'advanced';
} elseif ($memberStats['total_workouts'] > 5) {
    $fitnessLevel = 'intermediate';
}

// Handle workout completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'complete_workout' && isset($_POST['workout_id'])) {
        $workoutId = $_POST['workout_id'];
        $status = $_POST['status'] ?? 'completed';
        $notes = $_POST['notes'] ?? '';
        $rating = $_POST['rating'] ?? null;

        // Insert workout progress
        $query = "INSERT INTO member_workout_progress (member_id, workout_id, completion_date, status, notes, rating) 
                  VALUES (?, ?, CURDATE(), ?, ?, ?)";
        $stmt = $workout->conn->prepare($query);
        
        if ($stmt->execute([$memberId, $workoutId, $status, $notes, $rating])) {
            $success_message = "Workout marked as completed!";
        } else {
            $error_message = "Failed to mark workout as completed.";
        }
    }
}

// Get workouts based on member's fitness level
$recommendedWorkouts = $workout->getByDifficulty($fitnessLevel, 6);

// Get workouts by difficulty levels
$beginnerWorkouts = $workout->getByDifficulty('beginner', 4);
$intermediateWorkouts = $workout->getByDifficulty('intermediate', 4);
$advancedWorkouts = $workout->getByDifficulty('advanced', 4);

// Get all workouts for browsing with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;
$difficulty_filter = isset($_GET['difficulty']) ? $_GET['difficulty'] : null;
$purpose_filter = isset($_GET['purpose']) ? $_GET['purpose'] : null;

$allWorkouts = $workout->getAll($limit, $offset, $difficulty_filter, $purpose_filter);
$total_workouts = $workout->getCount($difficulty_filter, $purpose_filter);
$total_pages = ceil($total_workouts / $limit);

// Get member's workout progress history
$progressQuery = "SELECT mwp.*, w.title, w.difficulty_level, w.purpose 
                  FROM member_workout_progress mwp 
                  JOIN workouts w ON mwp.workout_id = w.workout_id 
                  WHERE mwp.member_id = ? 
                  ORDER BY mwp.completion_date DESC 
                  LIMIT 5";
$progressStmt = $workout->conn->prepare($progressQuery);
$progressStmt->execute([$memberId]);
$recentProgress = $progressStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workouts - ActiveCore Gym</title>
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="workouts.php">Workouts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="exercises.php">Exercises</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="workout_tracker.php">Workout Tracker</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="equipment.php">Equipment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="hire_coach.php">Hire Coach</a>
                    </li>
                </ul>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i><?php echo $memberData['first_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-running me-2"></i>Workout Plans</h2>
                <p class="text-muted">Discover workout plans tailored to your fitness level</p>
            </div>
        </div>

        <!-- Fitness Level Indicator -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1">Your Current Fitness Level</h5>
                                <p class="mb-0">Based on your workout history, we recommend: 
                                    <span class="badge bg-light text-dark fs-6"><?php echo ucfirst($fitnessLevel); ?></span>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <small>
                                    Total Workouts: <?php echo $memberStats['total_workouts']; ?> | 
                                    This Month: <?php echo $memberStats['this_month']; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Recommended Workouts -->
        <div class="row mb-4">
            <div class="col-12">
                <h4><i class="fas fa-star me-2"></i>Recommended for You</h4>
            </div>
        </div>
        <div class="row mb-5">
            <?php if (empty($recommendedWorkouts)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No workouts available for your fitness level yet. Check back soon!</div>
                </div>
            <?php else: ?>
                <?php foreach ($recommendedWorkouts as $workout_data): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 workout-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $workout_data['title']; ?></h5>
                                <p class="card-text"><?php echo substr($workout_data['description'], 0, 100) . '...'; ?></p>
                                
                                <div class="mb-2">
                                    <span class="badge bg-info"><?php echo ucfirst($workout_data['purpose']); ?></span>
                                    <?php
                                    $difficulty_class = [
                                        'beginner' => 'success',
                                        'intermediate' => 'warning',
                                        'advanced' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $difficulty_class[$workout_data['difficulty_level']]; ?>">
                                        <?php echo ucfirst($workout_data['difficulty_level']); ?>
                                    </span>
                                </div>
                                
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-clock me-1"></i> <?php echo $workout_data['duration_minutes']; ?> min
                                    <i class="fas fa-list ms-2 me-1"></i> <?php echo $workout_data['exercise_count']; ?> exercises
                                </div>
                                
                                <button type="button" class="btn btn-primary btn-sm" onclick="viewWorkout(<?php echo $workout_data['workout_id']; ?>)">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Browse by Difficulty -->
        <div class="row mb-4">
            <div class="col-12">
                <h4><i class="fas fa-filter me-2"></i>Browse by Difficulty</h4>
            </div>
        </div>

        <!-- Beginner Workouts -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="text-success"><i class="fas fa-seedling me-2"></i>Beginner Workouts</h5>
            </div>
            <?php foreach ($beginnerWorkouts as $workout_data): ?>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card h-100 workout-card">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $workout_data['title']; ?></h6>
                            <p class="card-text small"><?php echo substr($workout_data['description'], 0, 80) . '...'; ?></p>
                            <div class="text-muted small">
                                <i class="fas fa-clock me-1"></i> <?php echo $workout_data['duration_minutes']; ?> min
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="viewWorkout(<?php echo $workout_data['workout_id']; ?>)">
                                View
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Intermediate Workouts -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="text-warning"><i class="fas fa-fire me-2"></i>Intermediate Workouts</h5>
            </div>
            <?php foreach ($intermediateWorkouts as $workout_data): ?>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card h-100 workout-card">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $workout_data['title']; ?></h6>
                            <p class="card-text small"><?php echo substr($workout_data['description'], 0, 80) . '...'; ?></p>
                            <div class="text-muted small">
                                <i class="fas fa-clock me-1"></i> <?php echo $workout_data['duration_minutes']; ?> min
                            </div>
                            <button type="button" class="btn btn-outline-warning btn-sm mt-2" onclick="viewWorkout(<?php echo $workout_data['workout_id']; ?>)">
                                View
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Advanced Workouts -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="text-danger"><i class="fas fa-bolt me-2"></i>Advanced Workouts</h5>
            </div>
            <?php foreach ($advancedWorkouts as $workout_data): ?>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card h-100 workout-card">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $workout_data['title']; ?></h6>
                            <p class="card-text small"><?php echo substr($workout_data['description'], 0, 80) . '...'; ?></p>
                            <div class="text-muted small">
                                <i class="fas fa-clock me-1"></i> <?php echo $workout_data['duration_minutes']; ?> min
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick="viewWorkout(<?php echo $workout_data['workout_id']; ?>)">
                                View
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- All Workouts with Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <h4><i class="fas fa-th me-2"></i>All Workouts</h4>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <select class="form-select" name="difficulty">
                                    <option value="">All Difficulties</option>
                                    <option value="beginner" <?php echo $difficulty_filter === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                    <option value="intermediate" <?php echo $difficulty_filter === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="advanced" <?php echo $difficulty_filter === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="purpose">
                                    <option value="">All Purposes</option>
                                    <option value="strength" <?php echo $purpose_filter === 'strength' ? 'selected' : ''; ?>>Strength</option>
                                    <option value="cardio" <?php echo $purpose_filter === 'cardio' ? 'selected' : ''; ?>>Cardio</option>
                                    <option value="flexibility" <?php echo $purpose_filter === 'flexibility' ? 'selected' : ''; ?>>Flexibility</option>
                                    <option value="weight_loss" <?php echo $purpose_filter === 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                                    <option value="muscle_gain" <?php echo $purpose_filter === 'muscle_gain' ? 'selected' : ''; ?>>Muscle Gain</option>
                                    <option value="endurance" <?php echo $purpose_filter === 'endurance' ? 'selected' : ''; ?>>Endurance</option>
                                    <option value="recovery" <?php echo $purpose_filter === 'recovery' ? 'selected' : ''; ?>>Recovery</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="workouts.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Workouts Grid -->
        <div class="row mb-4">
            <?php if (empty($allWorkouts)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No workouts found matching your criteria.</div>
                </div>
            <?php else: ?>
                <?php foreach ($allWorkouts as $workout_data): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 workout-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $workout_data['title']; ?></h5>
                                <p class="card-text"><?php echo substr($workout_data['description'], 0, 100) . '...'; ?></p>
                                
                                <div class="mb-2">
                                    <span class="badge bg-info"><?php echo ucfirst($workout_data['purpose']); ?></span>
                                    <?php
                                    $difficulty_class = [
                                        'beginner' => 'success',
                                        'intermediate' => 'warning',
                                        'advanced' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $difficulty_class[$workout_data['difficulty_level']]; ?>">
                                        <?php echo ucfirst($workout_data['difficulty_level']); ?>
                                    </span>
                                </div>
                                
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-clock me-1"></i> <?php echo $workout_data['duration_minutes']; ?> min
                                    <i class="fas fa-list ms-2 me-1"></i> <?php echo $workout_data['exercise_count']; ?> exercises
                                </div>
                                
                                <button type="button" class="btn btn-primary btn-sm" onclick="viewWorkout(<?php echo $workout_data['workout_id']; ?>)">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Workout pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $difficulty_filter ? '&difficulty=' . $difficulty_filter : ''; ?><?php echo $purpose_filter ? '&purpose=' . $purpose_filter : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Recent Progress -->
        <div class="row mt-5">
            <div class="col-12">
                <h4><i class="fas fa-chart-line me-2"></i>Your Recent Progress</h4>
            </div>
            <?php if (empty($recentProgress)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No workout progress yet. Start with a workout above!</div>
                </div>
            <?php else: ?>
                <?php foreach ($recentProgress as $progress): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo $progress['title']; ?></h6>
                                <p class="card-text small">
                                    Completed: <?php echo date('M j, Y', strtotime($progress['completion_date'])); ?><br>
                                    Status: <span class="badge bg-success"><?php echo ucfirst($progress['status']); ?></span>
                                    <?php if ($progress['rating']): ?>
                                        <br>Rating: <?php echo str_repeat('⭐', $progress['rating']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Workout Details Modal -->
    <div class="modal fade" id="workoutModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Workout Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="workoutDetails">
                    <!-- Workout details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View workout details
        function viewWorkout(workoutId) {
            fetch(`workouts.php?action=view_workout&workout_id=${workoutId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('workoutDetails').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('workoutModal')).show();
                })
                .catch(error => console.error('Error:', error));
        }

        // Handle view workout request
        <?php if (isset($_GET['action']) && $_GET['action'] === 'view_workout' && isset($_GET['workout_id'])): ?>
            $view_workout = new Workout();
            $view_workout->workout_id = $_GET['workout_id'];
            if ($view_workout->readOne()) {
                $exercises = $view_workout->getExercises();
                ?>
                document.addEventListener('DOMContentLoaded', function() {
                    const workoutDetails = `
                        <div class="workout-detail-header">
                            <h6><?php echo $view_workout->title; ?></h6>
                            <div class="mb-3">
                                <span class="badge bg-info"><?php echo ucfirst($view_workout->purpose); ?></span>
                                <?php
                                $difficulty_class = [
                                    'beginner' => 'success',
                                    'intermediate' => 'warning',
                                    'advanced' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $difficulty_class[$view_workout->difficulty_level]; ?>">
                                    <?php echo ucfirst($view_workout->difficulty_level); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="workout-info mb-3">
                            <p><strong>Duration:</strong> <?php echo $view_workout->duration_minutes; ?> minutes</p>
                            <p><strong>Equipment:</strong> <?php echo $view_workout->equipment_required ?: 'None required'; ?></p>
                            <p><strong>Description:</strong> <?php echo $view_workout->description; ?></p>
                            <p><strong>Instructions:</strong> <?php echo $view_workout->instructions; ?></p>
                        </div>
                        
                        <hr>
                        
                        <div class="exercises-section">
                            <h6>Exercises:</h6>
                            <?php if (!empty($exercises)): ?>
                                <ol class="exercise-list">
                                    <?php foreach ($exercises as $exercise): ?>
                                        <li class="exercise-item mb-3">
                                            <strong><?php echo $exercise['name']; ?></strong>
                                            <div class="exercise-details small text-muted">
                                                Sets: <?php echo $exercise['sets']; ?>
                                                <?php if ($exercise['reps']): ?> | Reps: <?php echo $exercise['reps']; ?><?php endif; ?>
                                                <?php if ($exercise['duration_seconds']): ?> | Duration: <?php echo $exercise['duration_seconds']; ?>s<?php endif; ?>
                                                <?php if ($exercise['rest_seconds']): ?> | Rest: <?php echo $exercise['rest_seconds']; ?>s<?php endif; ?>
                                                <?php if ($exercise['muscle_group']): ?> | Target: <?php echo $exercise['muscle_group']; ?><?php endif; ?>
                                            </div>
                                            <?php if ($exercise['exercise_instructions']): ?>
                                                <div class="exercise-instructions small mt-1">
                                                    <em><?php echo $exercise['exercise_instructions']; ?></em>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($exercise['notes']): ?>
                                                <div class="exercise-notes small mt-1 text-info">
                                                    <strong>Note:</strong> <?php echo $exercise['notes']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php else: ?>
                                <p>No exercises added to this workout.</p>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <div class="workout-actions">
                            <form method="POST" onsubmit="return confirm('Mark this workout as completed?');">
                                <input type="hidden" name="action" value="complete_workout">
                                <input type="hidden" name="workout_id" value="<?php echo $view_workout->workout_id; ?>">
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status:</label>
                                    <select class="form-select" name="status" required>
                                        <option value="completed">Completed</option>
                                        <option value="partial">Partially Completed</option>
                                        <option value="skipped">Skipped</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rate this workout:</label>
                                    <select class="form-select" name="rating">
                                        <option value="">No Rating</option>
                                        <option value="1">1 Star</option>
                                        <option value="2">2 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="5">5 Stars</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes (optional):</label>
                                    <textarea class="form-control" name="notes" rows="2" placeholder="How did it go? Any feedback?"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Mark as Completed
                                </button>
                            </form>
                        </div>
                    `;
                    document.getElementById('workoutDetails').innerHTML = workoutDetails;
                    new bootstrap.Modal(document.getElementById('workoutModal')).show();
                });
                <?php
            }
        <?php endif; ?>
    </script>
</body>
</html>
