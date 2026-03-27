<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../models/WorkoutLog.php';
require_once __DIR__ . '/../../models/Exercise.php';

// Check if user is logged in and is a member
checkAccess('member');

$authController = new AuthController();
$workoutLog = new WorkoutLog();
$exercise = new Exercise();

$memberId = $_SESSION['member_id'];
$success = '';
$error = '';

// Handle workout log submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workoutData = [
        'member_id' => $memberId,
        'exercise_id' => intval($_POST['exercise_id'] ?? 0),
        'workout_date' => sanitizeInput($_POST['workout_date'] ?? date('Y-m-d')),
        'sets' => intval($_POST['sets'] ?? 0),
        'reps' => intval($_POST['reps'] ?? 0),
        'weight' => floatval($_POST['weight'] ?? 0),
        'duration_minutes' => intval($_POST['duration_minutes'] ?? 0),
        'notes' => sanitizeInput($_POST['notes'] ?? '')
    ];

    // Validate input
    if ($workoutData['exercise_id'] <= 0) {
        $error = 'Please select an exercise';
    } elseif ($workoutData['sets'] <= 0 || $workoutData['reps'] <= 0) {
        $error = 'Sets and reps must be greater than 0';
    } else {
        $workoutLog->member_id = $workoutData['member_id'];
        $workoutLog->exercise_id = $workoutData['exercise_id'];
        $workoutLog->workout_date = $workoutData['workout_date'];
        $workoutLog->sets = $workoutData['sets'];
        $workoutLog->reps = $workoutData['reps'];
        $workoutLog->weight = $workoutData['weight'];
        $workoutLog->duration_minutes = $workoutData['duration_minutes'];
        $workoutLog->notes = $workoutData['notes'];

        if ($workoutLog->create()) {
            $_SESSION['success'] = 'Workout logged successfully!';
            header('Location: workout_tracker.php');
            exit();
        } else {
            $error = 'Failed to log workout. Please try again.';
        }
    }
}

// Get exercises for dropdown
$exercises = $exercise->getAll(100, 0);

// Get recent workouts
$recentWorkouts = $workoutLog->getByMember($memberId, 10);

// Get workout statistics
$stats = $workoutLog->getMemberStats($memberId);

// Get pre-selected exercise if coming from exercises page
$preSelectedExercise = intval($_GET['exercise_id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Tracker - ActiveCore Gym</title>
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
                        <a class="nav-link" href="exercises.php">Exercises</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="equipment.php">Equipment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="hire_coach.php">Hire Coach</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="workout_tracker.php">Workout Tracker</a>
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

        <div class="row mb-4">
            <div class="col-12">
                <h1><i class="fas fa-clipboard-list me-2"></i>Workout Tracker</h1>
                <p class="text-muted">Log your workouts and track your fitness progress</p>
            </div>
        </div>

        <!-- Workout Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['total_workouts'] ?? 0; ?></h3>
                        <p class="mb-0">Total Workouts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['workout_days'] ?? 0; ?></h3>
                        <p class="mb-0">Workout Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['max_weight'] ?? 0; ?> kg</h3>
                        <p class="mb-0">Max Weight</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['total_duration'] ?? 0; ?> min</h3>
                        <p class="mb-0">Total Duration</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Log Workout Form -->
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Log New Workout</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="exercise_id" class="form-label">Exercise *</label>
                                <select class="form-select" id="exercise_id" name="exercise_id" required>
                                    <option value="">Select an exercise</option>
                                    <?php foreach ($exercises as $ex): ?>
                                        <option value="<?php echo $ex['exercise_id']; ?>" 
                                                <?php echo ($preSelectedExercise == $ex['exercise_id']) ? 'selected' : ''; ?>
                                                data-difficulty="<?php echo $ex['difficulty_level']; ?>"
                                                data-muscle="<?php echo $ex['muscle_group'] ?? ''; ?>">
                                            <?php echo htmlspecialchars($ex['name']); ?> - 
                                            <?php echo ucfirst($ex['difficulty_level']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="workout_date" class="form-label">Workout Date *</label>
                                <input type="date" class="form-control" id="workout_date" name="workout_date" 
                                       value="<?php echo htmlspecialchars($_POST['workout_date'] ?? date('Y-m-d')); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sets" class="form-label">Sets *</label>
                                    <input type="number" class="form-control" id="sets" name="sets" 
                                           value="<?php echo htmlspecialchars($_POST['sets'] ?? ''); ?>" min="1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reps" class="form-label">Reps *</label>
                                    <input type="number" class="form-control" id="reps" name="reps" 
                                           value="<?php echo htmlspecialchars($_POST['reps'] ?? ''); ?>" min="1" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" 
                                           value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>" min="0" step="0.5">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                                    <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                                           value="<?php echo htmlspecialchars($_POST['duration_minutes'] ?? ''); ?>" min="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Log Workout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Workouts -->
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Workouts</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="filterWorkouts('all')">All</button>
                            <button class="btn btn-outline-primary" onclick="filterWorkouts('today')">Today</button>
                            <button class="btn btn-outline-primary" onclick="filterWorkouts('week')">This Week</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentWorkouts)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="workoutsTable">
                                    <thead>
                                        <tr>
                                            <th>Exercise</th>
                                            <th>Sets × Reps</th>
                                            <th>Weight</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentWorkouts as $workout): ?>
                                            <tr data-date="<?php echo $workout['workout_date']; ?>">
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($workout['exercise_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <span class="badge bg-<?php echo ($workout['difficulty_level'] === 'beginner') ? 'success' : (($workout['difficulty_level'] === 'intermediate') ? 'warning' : 'danger'); ?>">
                                                                <?php echo ucfirst($workout['difficulty_level']); ?>
                                                            </span>
                                                            <?php echo htmlspecialchars($workout['muscle_group'] ?? ''); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo $workout['sets']; ?> × <?php echo $workout['reps']; ?>
                                                    <?php if ($workout['duration_minutes']): ?>
                                                        <br><small class="text-muted"><?php echo $workout['duration_minutes']; ?> min</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($workout['weight']): ?>
                                                        <?php echo $workout['weight']; ?> kg
                                                    <?php else: ?>
                                                        <span class="text-muted">Bodyweight</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo formatDate($workout['workout_date']); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="editWorkout(<?php echo $workout['log_id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="deleteWorkout(<?php echo $workout['log_id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h5>No workouts logged yet</h5>
                                <p class="text-muted">Start tracking your fitness journey by logging your first workout!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Charts -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Progress Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Most Performed Exercises</h6>
                                <?php
                                $mostPerformed = $workoutLog->getMostPerformedExercises($memberId, 5);
                                if (!empty($mostPerformed)):
                                ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($mostPerformed as $exercise): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><?php echo htmlspecialchars($exercise['name']); ?></span>
                                                <span class="badge bg-primary"><?php echo $exercise['frequency']; ?> times</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted">No data available yet</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6>Muscle Group Distribution</h6>
                                <?php
                                $muscleDistribution = $workoutLog->getMuscleGroupDistribution($memberId);
                                if (!empty($muscleDistribution)):
                                ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($muscleDistribution as $group): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><?php echo htmlspecialchars($group['muscle_group']); ?></span>
                                                <span class="badge bg-info"><?php echo $group['frequency']; ?> workouts</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted">No data available yet</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Exercise selection handler
        document.getElementById('exercise_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const difficulty = selectedOption.dataset.difficulty;
            const muscle = selectedOption.dataset.muscle;
            
            // You can display exercise info here if needed
            console.log('Selected:', selectedOption.text, 'Difficulty:', difficulty, 'Muscle:', muscle);
        });

        // Filter workouts
        function filterWorkouts(period) {
            const rows = document.querySelectorAll('#workoutsTable tbody tr');
            const today = new Date();
            
            rows.forEach(row => {
                const workoutDate = new Date(row.dataset.date);
                let show = false;
                
                switch(period) {
                    case 'all':
                        show = true;
                        break;
                    case 'today':
                        show = workoutDate.toDateString() === today.toDateString();
                        break;
                    case 'week':
                        const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                        show = workoutDate >= weekAgo;
                        break;
                }
                
                row.style.display = show ? '' : 'none';
            });
        }

        // Edit workout (placeholder function)
        function editWorkout(logId) {
            // Implement edit functionality
            alert('Edit functionality coming soon!');
        }

        // Delete workout
        function deleteWorkout(logId) {
            if (confirm('Are you sure you want to delete this workout?')) {
                // Implement delete functionality
                fetch(`delete_workout.php?id=${logId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete workout');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting workout');
                });
            }
        }

        // Set today's date as default
        document.getElementById('workout_date').valueAsDate = new Date();
    </script>
</body>
</html>
