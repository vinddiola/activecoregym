<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Workout.php';
require_once __DIR__ . '/../../models/Exercise.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Set page variables for header
$page_title = 'Workout Management';
$current_page = 'workouts';

$workout = new Workout();
$exercise = new Exercise();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_workout':
            $workout->title = sanitizeInput($_POST['title'] ?? '');
            $workout->description = sanitizeInput($_POST['description'] ?? '');
            $workout->purpose = sanitizeInput($_POST['purpose'] ?? '');
            $workout->difficulty_level = sanitizeInput($_POST['difficulty_level'] ?? '');
            $workout->duration_minutes = intval($_POST['duration_minutes'] ?? 0);
            $workout->equipment_required = sanitizeInput($_POST['equipment_required'] ?? '');
            $workout->instructions = sanitizeInput($_POST['instructions'] ?? '');
            $workout->is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (empty($workout->title) || empty($workout->purpose) || empty($workout->difficulty_level)) {
                $error_message = 'Please fill in all required fields';
            } else {
                if ($workout->create()) {
                    $success_message = 'Workout created successfully!';
                    // Handle exercises if provided
                    if (isset($_POST['exercises']) && is_array($_POST['exercises'])) {
                        foreach ($_POST['exercises'] as $exercise_data) {
                            if (!empty($exercise_data['exercise_id'])) {
                                $workout->addExercise($exercise_data['exercise_id'], $exercise_data);
                            }
                        }
                    }
                } else {
                    $error_message = 'Failed to create workout';
                }
            }
            break;
            
        case 'update_workout':
            $workout_id = intval($_POST['workout_id'] ?? 0);
            if ($workout_id > 0) {
                $workout->workout_id = $workout_id;
                $workout->title = sanitizeInput($_POST['title'] ?? '');
                $workout->description = sanitizeInput($_POST['description'] ?? '');
                $workout->purpose = sanitizeInput($_POST['purpose'] ?? '');
                $workout->difficulty_level = sanitizeInput($_POST['difficulty_level'] ?? '');
                $workout->duration_minutes = intval($_POST['duration_minutes'] ?? 0);
                $workout->equipment_required = sanitizeInput($_POST['equipment_required'] ?? '');
                $workout->instructions = sanitizeInput($_POST['instructions'] ?? '');
                $workout->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if ($workout->update()) {
                    $success_message = 'Workout updated successfully!';
                } else {
                    $error_message = 'Failed to update workout';
                }
            }
            break;
            
        case 'delete_workout':
            $workout_id = intval($_POST['workout_id'] ?? 0);
            if ($workout_id > 0) {
                $workout->workout_id = $workout_id;
                if ($workout->delete()) {
                    $success_message = 'Workout deleted successfully!';
                } else {
                    $error_message = 'Failed to delete workout';
                }
            }
            break;
    }
}

// Handle GET requests
$difficulty_filter = $_GET['difficulty'] ?? '';
$purpose_filter = $_GET['purpose'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Handle view workout request
if (isset($_GET['action']) && $_GET['action'] === 'view_workout' && isset($_GET['workout_id'])) {
    $view_workout = new Workout();
    $view_workout->workout_id = $_GET['workout_id'];
    if ($view_workout->readOne()) {
        $exercises = $view_workout->getExercises();
        
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'workout' => [
                'title' => $view_workout->title,
                'purpose' => ucfirst($view_workout->purpose),
                'difficulty' => ucfirst($view_workout->difficulty_level),
                'duration' => $view_workout->duration_minutes,
                'equipment' => $view_workout->equipment_required ?: 'None',
                'description' => $view_workout->description,
                'instructions' => $view_workout->instructions,
                'exercises' => $exercises
            ]
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Workout not found']);
    }
    exit;
}

// Get workouts
$workouts = $workout->getAll($limit, $offset, $difficulty_filter, $purpose_filter);
$total_workouts = $workout->getCount($difficulty_filter, $purpose_filter);
$total_pages = ceil($total_workouts / $limit);

// Get statistics
$workoutStats = $workout->getStats();

// Get all exercises for dropdown
$allExercises = $exercise->getAll();

// Handle edit request
$edit_workout = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $workout->workout_id = $_GET['edit'];
    if ($workout->readOne()) {
        $edit_workout = [
            'workout_id' => $workout->workout_id,
            'title' => $workout->title,
            'description' => $workout->description,
            'purpose' => $workout->purpose,
            'difficulty_level' => $workout->difficulty_level,
            'duration_minutes' => $workout->duration_minutes,
            'equipment_required' => $workout->equipment_required,
            'instructions' => $workout->instructions,
            'is_active' => $workout->is_active,
            'exercises' => $workout->getExercises()
        ];
    }
}

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-gold"><i class="fas fa-running me-2"></i>Workout Management</h2>
        <p class="text-light-gold">Create and manage workout plans for gym members</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center bg-gray">
            <div class="card-body">
                <h5 class="card-title text-gold"><?php echo $workoutStats['total_workouts']; ?></h5>
                <p class="card-text text-light-gold">Total Workouts</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-gray">
            <div class="card-body">
                <h5 class="card-title text-success"><?php echo $workoutStats['beginner_count']; ?></h5>
                <p class="card-text text-light-gold">Beginner Workouts</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-gray">
            <div class="card-body">
                <h5 class="card-title text-warning"><?php echo $workoutStats['intermediate_count']; ?></h5>
                <p class="card-text text-light-gold">Intermediate Workouts</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center bg-gray">
            <div class="card-body">
                <h5 class="card-title text-danger"><?php echo $workoutStats['advanced_count']; ?></h5>
                <p class="card-text text-light-gold">Advanced Workouts</p>
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

<!-- Create/Edit Workout Form -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    <?php echo $edit_workout ? 'Edit Workout' : 'Create New Workout'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="workoutForm">
                    <input type="hidden" name="action" value="<?php echo $edit_workout ? 'update_workout' : 'create_workout'; ?>">
                    <?php if ($edit_workout): ?>
                        <input type="hidden" name="workout_id" value="<?php echo $edit_workout['workout_id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Workout Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       value="<?php echo $edit_workout['title'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration_minutes" class="form-label">Duration (minutes) *</label>
                                <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" required min="1"
                                       value="<?php echo $edit_workout['duration_minutes'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="purpose" class="form-label">Purpose *</label>
                                <select class="form-select" name="purpose" id="purpose" required>
                                    <option value="">Select Purpose</option>
                                    <option value="strength" <?php echo ($edit_workout['purpose'] ?? '') === 'strength' ? 'selected' : ''; ?>>Strength</option>
                                    <option value="cardio" <?php echo ($edit_workout['purpose'] ?? '') === 'cardio' ? 'selected' : ''; ?>>Cardio</option>
                                    <option value="flexibility" <?php echo ($edit_workout['purpose'] ?? '') === 'flexibility' ? 'selected' : ''; ?>>Flexibility</option>
                                    <option value="weight_loss" <?php echo ($edit_workout['purpose'] ?? '') === 'weight_loss' ? 'selected' : ''; ?>>Weight Loss</option>
                                    <option value="muscle_gain" <?php echo ($edit_workout['purpose'] ?? '') === 'muscle_gain' ? 'selected' : ''; ?>>Muscle Gain</option>
                                    <option value="endurance" <?php echo ($edit_workout['purpose'] ?? '') === 'endurance' ? 'selected' : ''; ?>>Endurance</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="difficulty_level" class="form-label">Difficulty Level *</label>
                                <select class="form-select" name="difficulty_level" id="difficulty_level" required>
                                    <option value="">Select Difficulty</option>
                                    <option value="beginner" <?php echo ($edit_workout['difficulty_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                    <option value="intermediate" <?php echo ($edit_workout['difficulty_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="advanced" <?php echo ($edit_workout['difficulty_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="3"
                                  placeholder="Enter workout description"><?php echo $edit_workout['description'] ?? ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="equipment_required" class="form-label">Equipment Required</label>
                        <textarea class="form-control" name="equipment_required" id="equipment_required" rows="2"
                                  placeholder="List required equipment (separated by commas)"><?php echo $edit_workout['equipment_required'] ?? ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="instructions" class="form-label">Instructions</label>
                        <textarea class="form-control" name="instructions" id="instructions" rows="4"
                                  placeholder="Enter workout instructions and guidelines"><?php echo $edit_workout['instructions'] ?? ''; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                   <?php echo ($edit_workout['is_active'] ?? 1) == 1 ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Active (Available for members)
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="mb-3">Exercises</h6>
                        <div id="exercisesContainer">
                            <?php if ($edit_workout && !empty($edit_workout['exercises'])): ?>
                                <?php foreach ($edit_workout['exercises'] as $index => $ex): ?>
                                    <div class="exercise-item mb-3 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <select class="form-select" name="exercises[<?php echo $index; ?>][exercise_id]" required>
                                                    <option value="">Select Exercise</option>
                                                    <?php foreach ($allExercises as $exercise): ?>
                                                        <option value="<?php echo $exercise['exercise_id']; ?>" 
                                                                <?php echo $ex['exercise_id'] == $exercise['exercise_id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($exercise['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control" name="exercises[<?php echo $index; ?>][sets]" 
                                                       placeholder="Sets" value="<?php echo $ex['sets']; ?>" min="1" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control" name="exercises[<?php echo $index; ?>][reps]" 
                                                       placeholder="Reps" value="<?php echo $ex['reps']; ?>" min="1" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control" name="exercises[<?php echo $index; ?>][rest_seconds]" 
                                                       placeholder="Rest (sec)" value="<?php echo $ex['rest_seconds']; ?>" min="0">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm remove-exercise">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="addExercise">
                            <i class="fas fa-plus"></i> Add Exercise
                        </button>
                    </div>

                    <div class="text-end">
                        <a href="workouts.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $edit_workout ? 'Update Workout' : 'Create Workout'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
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

<!-- Workout List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Workout Plans</h5>
                <div>
                    <span class="badge bg-primary me-2"><?php echo count($workouts); ?> workouts</span>
                    <a href="workouts.php?action=add" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Workout
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Purpose</th>
                                <th>Difficulty</th>
                                <th>Duration</th>
                                <th>Exercises</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($workouts)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No workouts found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($workouts as $workout_data): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $workout_data['title']; ?></strong><br>
                                            <small class="text-muted"><?php echo substr($workout_data['description'], 0, 50) . '...'; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($workout_data['purpose']); ?></span>
                                        </td>
                                        <td>
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
                                        </td>
                                        <td><?php echo $workout_data['duration_minutes']; ?> min</td>
                                        <td><?php echo $workout_data['exercise_count']; ?> exercises</td>
                                        <td>
                                            <?php if ($workout_data['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick="viewWorkout(<?php echo $workout_data['workout_id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="workouts.php?edit=<?php echo $workout_data['workout_id']; ?>" class="btn btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteWorkout(<?php echo $workout_data['workout_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Workout pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $difficulty_filter ? '&difficulty=' . $difficulty_filter : ''; ?><?php echo $purpose_filter ? '&purpose=' . $purpose_filter : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
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

<script>
// Exercise management
let exerciseIndex = <?php echo $edit_workout ? count($edit_workout['exercises']) : 0; ?>;

document.getElementById('addExercise').addEventListener('click', function() {
    exerciseIndex++;
    const exerciseHtml = `
        <div class="exercise-item mb-3 p-3 border rounded">
            <div class="row">
                <div class="col-md-4">
                    <select class="form-select" name="exercises[${exerciseIndex}][exercise_id]" required>
                        <option value="">Select Exercise</option>
                        <?php foreach ($allExercises as $exercise): ?>
                            <option value="<?php echo $exercise['exercise_id']; ?>"><?php echo htmlspecialchars($exercise['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="exercises[${exerciseIndex}][sets]" placeholder="Sets" min="1" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="exercises[${exerciseIndex}][reps]" placeholder="Reps" min="1" required>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="exercises[${exerciseIndex}][rest_seconds]" placeholder="Rest (sec)" min="0">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-exercise">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('exercisesContainer').insertAdjacentHTML('beforeend', exerciseHtml);
});

// Remove exercise
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-exercise')) {
        e.target.closest('.exercise-item').remove();
    }
});

// View workout
function viewWorkout(workoutId) {
    fetch(`workouts.php?action=view_workout&workout_id=${workoutId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const workout = data.workout;
                let exercisesHtml = '';
                
                if (workout.exercises && workout.exercises.length > 0) {
                    exercisesHtml = '<ol>';
                    workout.exercises.forEach(exercise => {
                        exercisesHtml += `
                            <li>
                                <strong>${exercise.name}</strong><br>
                                Sets: ${exercise.sets}
                                ${exercise.reps ? ` | Reps: ${exercise.reps}` : ''}
                                ${exercise.duration_seconds ? ` | Duration: ${exercise.duration_seconds}s` : ''}
                                ${exercise.rest_seconds ? ` | Rest: ${exercise.rest_seconds}s` : ''}
                                ${exercise.notes ? `<br><small>${exercise.notes}</small>` : ''}
                            </li>
                        `;
                    });
                    exercisesHtml += '</ol>';
                } else {
                    exercisesHtml = '<p>No exercises added to this workout.</p>';
                }
                
                const workoutDetails = `
                    <h6>${workout.title}</h6>
                    <p><strong>Purpose:</strong> ${workout.purpose}</p>
                    <p><strong>Difficulty:</strong> ${workout.difficulty}</p>
                    <p><strong>Duration:</strong> ${workout.duration} minutes</p>
                    <p><strong>Equipment:</strong> ${workout.equipment}</p>
                    <p><strong>Description:</strong> ${workout.description}</p>
                    <p><strong>Instructions:</strong> ${workout.instructions}</p>
                    <hr>
                    <h6>Exercises:</h6>
                    ${exercisesHtml}
                `;
                
                document.getElementById('workoutDetails').innerHTML = workoutDetails;
                new bootstrap.Modal(document.getElementById('workoutModal')).show();
            } else {
                alert('Error: ' + (data.message || 'Failed to load workout details'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading workout details');
        });
}

// Delete workout
function deleteWorkout(workoutId) {
    if (confirm('Are you sure you want to delete this workout?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_workout">
            <input type="hidden" name="workout_id" value="${workoutId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
// Include footer
require_once __DIR__ . '/footer.php';
?>
