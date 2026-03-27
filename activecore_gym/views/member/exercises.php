<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Exercise.php';
require_once __DIR__ . '/../../models/Equipment.php';

// Check if user is logged in and is a member
checkAccess('member');

$exercise = new Exercise();
$equipment = new Equipment();

// Get filter parameters
$difficulty = $_GET['difficulty'] ?? '';
$muscle_group = $_GET['muscle_group'] ?? '';
$search = $_GET['search'] ?? '';

// Get exercises based on filters
if ($difficulty) {
    $exercises = $exercise->getByDifficulty($difficulty);
} elseif ($muscle_group) {
    $exercises = $exercise->getByMuscleGroup($muscle_group);
} elseif ($search) {
    $exercises = $exercise->search($search);
} else {
    $exercises = $exercise->getAll(50, 0);
}

// Get muscle groups for filter
$muscleGroups = $exercise->getMuscleGroups();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercises - ActiveCore Gym</title>
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
                        <a class="nav-link active" href="exercises.php">Exercises</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="equipment.php">Equipment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="hire_coach.php">Hire Coach</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="workout_tracker.php">Workout Tracker</a>
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
                <h1><i class="fas fa-dumbbell me-2"></i>Exercise Library</h1>
                <p class="text-muted">Browse exercises by difficulty level, muscle group, or search for specific exercises</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="difficulty" class="form-label">Difficulty Level</label>
                                    <select class="form-select" id="difficulty" name="difficulty">
                                        <option value="">All Levels</option>
                                        <option value="beginner" <?php echo $difficulty === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                        <option value="intermediate" <?php echo $difficulty === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                        <option value="advanced" <?php echo $difficulty === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="muscle_group" class="form-label">Muscle Group</label>
                                    <select class="form-select" id="muscle_group" name="muscle_group">
                                        <option value="">All Groups</option>
                                        <?php foreach ($muscleGroups as $group): ?>
                                            <option value="<?php echo $group; ?>" <?php echo $muscle_group === $group ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($group); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search Exercises</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or muscle group...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="btn-group" role="group">
                    <a href="?difficulty=beginner" class="btn <?php echo $difficulty === 'beginner' ? 'btn-success' : 'btn-outline-success'; ?>">
                        <i class="fas fa-seedling me-2"></i>Beginner
                    </a>
                    <a href="?difficulty=intermediate" class="btn <?php echo $difficulty === 'intermediate' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        <i class="fas fa-fire me-2"></i>Intermediate
                    </a>
                    <a href="?difficulty=advanced" class="btn <?php echo $difficulty === 'advanced' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                        <i class="fas fa-bolt me-2"></i>Advanced
                    </a>
                    <a href="?" class="btn <?php echo (!$difficulty && !$muscle_group && !$search) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-list me-2"></i>All Exercises
                    </a>
                </div>
            </div>
        </div>

        <!-- Exercise Cards -->
        <div class="row">
            <?php if (!empty($exercises)): ?>
                <?php foreach ($exercises as $exercise): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 exercise-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title"><?php echo htmlspecialchars($exercise['name']); ?></h5>
                                    <span class="badge bg-<?php echo ($exercise['difficulty_level'] === 'beginner') ? 'success' : (($exercise['difficulty_level'] === 'intermediate') ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($exercise['difficulty_level']); ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="badge bg-secondary me-2">
                                        <i class="fas fa-bullseye me-1"></i><?php echo htmlspecialchars($exercise['muscle_group'] ?? 'General'); ?>
                                    </span>
                                    <?php if (!empty($exercise['equipment_needed']) && $exercise['equipment_needed'] !== 'None'): ?>
                                        <span class="badge bg-info">
                                            <i class="fas fa-tools me-1"></i><?php echo htmlspecialchars($exercise['equipment_needed']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Bodyweight
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($exercise['instructions'], 0, 150)) . '...'; ?>
                                </p>

                                <?php if (!empty($exercise['equipment_list'])): ?>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <strong>Equipment:</strong> <?php echo htmlspecialchars($exercise['equipment_list']); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm" onclick="showExerciseDetails(<?php echo $exercise['exercise_id']; ?>)">
                                        <i class="fas fa-info-circle me-1"></i>Details
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="addToWorkout(<?php echo $exercise['exercise_id']; ?>)">
                                        <i class="fas fa-plus me-1"></i>Add to Workout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No exercises found</h4>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                        <a href="?" class="btn btn-primary">View All Exercises</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Exercise Details Modal -->
    <div class="modal fade" id="exerciseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalExerciseTitle">Exercise Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalExerciseContent">
                    <!-- Content will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="modalAddToWorkout">
                        <i class="fas fa-plus me-1"></i>Add to Workout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentExerciseId = null;

        function showExerciseDetails(exerciseId) {
            currentExerciseId = exerciseId;
            
            // Fetch exercise details via AJAX
            fetch(`get_exercise_details.php?id=${exerciseId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalExerciseTitle').textContent = data.name;
                    document.getElementById('modalExerciseContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Instructions</h6>
                                <p>${data.instructions}</p>
                                
                                <h6 class="mt-3">Target Muscles</h6>
                                <p>${data.muscle_group || 'General'}</p>
                                
                                <h6 class="mt-3">Equipment Needed</h6>
                                <p>${data.equipment_needed || 'None'}</p>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <span class="badge bg-${getDifficultyColor(data.difficulty_level)} fs-6">
                                        ${data.difficulty_level}
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>Added: ${data.created_at}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('modalAddToWorkout').onclick = function() {
                        addToWorkout(exerciseId);
                        bootstrap.Modal.getInstance(document.getElementById('exerciseModal')).hide();
                    };
                    
                    new bootstrap.Modal(document.getElementById('exerciseModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading exercise details');
                });
        }

        function getDifficultyColor(level) {
            switch(level) {
                case 'beginner': return 'success';
                case 'intermediate': return 'warning';
                case 'advanced': return 'danger';
                default: return 'secondary';
            }
        }

        function addToWorkout(exerciseId) {
            // Redirect to workout tracker with exercise pre-selected
            window.location.href = `workout_tracker.php?exercise_id=${exerciseId}`;
        }
    </script>
</body>
</html>
