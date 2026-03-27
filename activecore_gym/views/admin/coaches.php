<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Coach.php';
require_once __DIR__ . '/../../models/User.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Set page variables for header
$page_title = 'Coaches Management';
$current_page = 'coaches';

$coach = new Coach();
$user = new User();

// Handle coach actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_coach':
            // Validate required fields
            $required = ['first_name', 'last_name', 'username', 'email', 'password', 'specialization', 'experience_years'];
            $missing = [];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                }
            }
            
            if (!empty($missing)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
                exit;
            }
            
            // Create user account first
            $user->username = $_POST['username'];
            $user->email = $_POST['email'];
            $user->password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $user->user_type = 'coach';
            
            if ($user->create()) {
                // Create coach profile
                $coach->user_id = $user->getLastInsertId();
                $coach->first_name = $_POST['first_name'];
                $coach->last_name = $_POST['last_name'];
                $coach->specialization = $_POST['specialization'];
                $coach->experience_years = intval($_POST['experience_years']);
                $coach->bio = $_POST['bio'] ?? '';
                $coach->phone = $_POST['phone'] ?? '';
                $coach->is_available = isset($_POST['is_available']) ? 1 : 0;
                
                if ($coach->create()) {
                    echo json_encode(['success' => true, 'message' => 'Coach added successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to create coach profile']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create user account']);
            }
            exit;
            
        case 'deactivate':
            $coachId = intval($_POST['coach_id'] ?? 0);
            if ($coachId > 0) {
                $coachData = $coach->getById($coachId);
                if ($coachData) {
                    $user->user_id = $coachData['user_id'];
                    $user->deactivate();
                    $_SESSION['success'] = 'Coach deactivated successfully';
                }
            }
            break;
        case 'activate':
            $coachId = intval($_POST['coach_id'] ?? 0);
            if ($coachId > 0) {
                $coachData = $coach->getById($coachId);
                if ($coachData) {
                    $user->user_id = $coachData['user_id'];
                    $user->activate();
                    $_SESSION['success'] = 'Coach activated successfully';
                }
            }
            break;
        case 'toggle_availability':
            $coachId = intval($_POST['coach_id'] ?? 0);
            if ($coachId > 0) {
                $coachData = $coach->getById($coachId);
                if ($coachData) {
                    $coach->coach_id = $coachId;
                    $coach->is_available = !$coachData['is_available'];
                    $coach->update();
                    $_SESSION['success'] = 'Coach availability updated successfully';
                }
            }
            break;
    }
    header('Location: coaches.php');
    exit();
}

// Get all coaches
$coaches = $coach->getAll();

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-gold"><i class="fas fa-user-tie me-2"></i>Coaches Management</h2>
        <p class="text-light-gold">Manage gym coaches and their profiles</p>
    </div>
</div>

<!-- Coaches Table -->
<div class="card bg-gray">
    <div class="card-header bg-dark-gray">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-gold"><i class="fas fa-list me-2"></i>All Coaches</h5>
            <div>
                <button class="btn btn-sm btn-outline-success" onclick="addCoach()">
                    <i class="fas fa-plus me-1"></i>Add Coach
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="exportCoaches()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
                        <?php if (!empty($coaches)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="coachesTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Specialization</th>
                                            <th>Experience</th>
                                            <th>Rating</th>
                                            <th>Availability</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($coaches as $coach): ?>
                                            <tr>
                                                <td>#<?php echo $coach['coach_id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($coach['first_name'] . ' ' . $coach['last_name']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($coach['username']); ?></td>
                                                <td><?php echo htmlspecialchars($coach['email']); ?></td>
                                                <td><?php echo htmlspecialchars($coach['specialization'] ?? 'General'); ?></td>
                                                <td><?php echo $coach['experience_years']; ?> years</td>
                                                <td>
                                                    <div>
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star <?php echo $i <= $coach['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                        <?php endfor; ?>
                                                        <small class="text-muted">(<?php echo number_format($coach['rating'], 1); ?>)</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $coach['is_available'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $coach['is_available'] ? 'Available' : 'Unavailable'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($coach['is_active']): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="deactivate">
                                                                <input type="hidden" name="coach_id" value="<?php echo $coach['coach_id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Are you sure you want to deactivate this coach?')">
                                                                    <i class="fas fa-pause"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="activate">
                                                                <input type="hidden" name="coach_id" value="<?php echo $coach['coach_id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Are you sure you want to activate this coach?')">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="toggle_availability">
                                                            <input type="hidden" name="coach_id" value="<?php echo $coach['coach_id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-<?php echo $coach['is_available'] ? 'info' : 'secondary'; ?>" title="<?php echo $coach['is_available'] ? 'Set Unavailable' : 'Set Available'; ?>">
                                                                <i class="fas fa-<?php echo $coach['is_available'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                            </button>
                                                        </form>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewCoachDetails(<?php echo $coach['coach_id']; ?>)">
                                                            <i class="fas fa-info-circle"></i>
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
                                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                                <h4>No coaches found</h4>
                                <p class="text-muted">No coaches have been added yet</p>
                                <button class="btn btn-primary" onclick="addCoach()">
                                    <i class="fas fa-plus me-2"></i>Add First Coach
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coach Details Modal -->
    <div class="modal fade" id="coachModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Coach Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="coachModalContent">
                    <!-- Content will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewCoachDetails(coachId) {
            fetch(`get_coach_details.php?id=${coachId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('coachModalContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Personal Information</h6>
                                <p><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                                <p><strong>Username:</strong> ${data.username}</p>
                                <p><strong>Email:</strong> ${data.email}</p>
                                <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Professional Details</h6>
                                <p><strong>Coach ID:</strong> #${data.coach_id}</p>
                                <p><strong>Specialization:</strong> ${data.specialization || 'General Fitness'}</p>
                                <p><strong>Experience:</strong> ${data.experience_years} years</p>
                                <p><strong>Rating:</strong> ${data.rating} ⭐</p>
                                <p><strong>Availability:</strong> <span class="badge bg-${data.is_available ? 'success' : 'secondary'}">${data.is_available ? 'Available' : 'Unavailable'}</span></p>
                            </div>
                        </div>
                    `;
                    
                    new bootstrap.Modal(document.getElementById('coachModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading coach details');
                });
        }

        function addCoach() {
            const modal = new bootstrap.Modal(document.getElementById('addCoachModal'));
            modal.show();
        }

        function saveCoach() {
            const form = document.getElementById('addCoachForm');
            const formData = new FormData(form);
            
            fetch('coaches.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Coach added successfully!');
                    location.reload();
                } else {
                    alert('Error adding coach: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding coach');
            });
        }

        function exportCoaches() {
            alert('Export functionality coming soon!');
        }
    </script>

<!-- Add Coach Modal -->
<div class="modal fade" id="addCoachModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Coach</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCoachForm">
                    <input type="hidden" name="action" value="add_coach">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <select class="form-select" id="specialization" name="specialization" required>
                                <option value="">Select Specialization</option>
                                <option value="General Fitness">General Fitness</option>
                                <option value="Weight Training">Weight Training</option>
                                <option value="Cardio">Cardio</option>
                                <option value="Yoga">Yoga</option>
                                <option value="CrossFit">CrossFit</option>
                                <option value="Personal Training">Personal Training</option>
                                <option value="Nutrition">Nutrition</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="experience_years" class="form-label">Experience (Years)</label>
                            <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_available" name="is_available" checked>
                            <label class="form-check-label" for="is_available">
                                Available for training sessions
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCoach()">
                    <i class="fas fa-save me-2"></i>Add Coach
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/footer.php';
?>
