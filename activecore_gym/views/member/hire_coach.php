<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Coach.php';
require_once __DIR__ . '/../../models/Session.php';

// Check if user is logged in and is a member
checkAccess('member');

$coach = new Coach();
$session = new Session();

$memberId = $_SESSION['member_id'];
$success = '';
$error = '';

// Handle session booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionData = [
        'coach_id' => intval($_POST['coach_id'] ?? 0),
        'member_id' => $memberId,
        'session_date' => sanitizeInput($_POST['session_date'] ?? ''),
        'session_time' => sanitizeInput($_POST['session_time'] ?? ''),
        'notes' => sanitizeInput($_POST['notes'] ?? '')
    ];

    // Validate input
    if ($sessionData['coach_id'] <= 0) {
        $error = 'Please select a coach';
    } elseif (empty($sessionData['session_date']) || empty($sessionData['session_time'])) {
        $error = 'Please select date and time for the session';
    } elseif (strtotime($sessionData['session_date']) < strtotime(date('Y-m-d'))) {
        $error = 'Cannot book sessions for past dates';
    } else {
        // Check if coach is available at that time
        if (!$session->isCoachAvailable($sessionData['coach_id'], $sessionData['session_date'], $sessionData['session_time'])) {
            $error = 'Coach is not available at the selected time. Please choose a different time.';
        } else {
            $session->coach_id = $sessionData['coach_id'];
            $session->member_id = $sessionData['member_id'];
            $session->session_date = $sessionData['session_date'];
            $session->session_time = $sessionData['session_time'];
            $session->status = 'pending';
            $session->notes = $sessionData['notes'];

            if ($session->create()) {
                $_SESSION['success'] = 'Session booked successfully! The coach will confirm your booking.';
                header('Location: hire_coach.php');
                exit();
            } else {
                $error = 'Failed to book session. Please try again.';
            }
        }
    }
}

// Get available coaches
$availableCoaches = $coach->getAvailableCoaches();

// Get member's upcoming sessions
$upcomingSessions = $session->getUpcomingSessions($memberId);

// Get member's session history
$sessionHistory = $session->getByMember($memberId, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hire Coach - ActiveCore Gym</title>
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
                        <a class="nav-link active" href="hire_coach.php">Hire Coach</a>
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
                <h1><i class="fas fa-user-tie me-2"></i>Hire a Coach</h1>
                <p class="text-muted">Book personal training sessions with our expert coaches</p>
            </div>
        </div>

        <!-- Book Session Form -->
        <div class="row mb-4">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Book a Session</h5>
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
                                <label for="coach_id" class="form-label">Select Coach *</label>
                                <select class="form-select" id="coach_id" name="coach_id" required>
                                    <option value="">Choose a coach</option>
                                    <?php foreach ($availableCoaches as $coach): ?>
                                        <option value="<?php echo $coach['coach_id']; ?>">
                                            <?php echo htmlspecialchars($coach['first_name'] . ' ' . $coach['last_name']); ?> - 
                                            <?php echo htmlspecialchars($coach['specialization'] ?? 'General Fitness'); ?>
                                            (<?php echo number_format($coach['rating'], 1); ?> ⭐)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="session_date" class="form-label">Session Date *</label>
                                <input type="date" class="form-control" id="session_date" name="session_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="session_time" class="form-label">Session Time *</label>
                                <select class="form-select" id="session_time" name="session_time" required>
                                    <option value="">Select time</option>
                                    <option value="06:00:00">6:00 AM</option>
                                    <option value="07:00:00">7:00 AM</option>
                                    <option value="08:00:00">8:00 AM</option>
                                    <option value="09:00:00">9:00 AM</option>
                                    <option value="10:00:00">10:00 AM</option>
                                    <option value="11:00:00">11:00 AM</option>
                                    <option value="12:00:00">12:00 PM</option>
                                    <option value="13:00:00">1:00 PM</option>
                                    <option value="14:00:00">2:00 PM</option>
                                    <option value="15:00:00">3:00 PM</option>
                                    <option value="16:00:00">4:00 PM</option>
                                    <option value="17:00:00">5:00 PM</option>
                                    <option value="18:00:00">6:00 PM</option>
                                    <option value="19:00:00">7:00 PM</option>
                                    <option value="20:00:00">8:00 PM</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Any specific goals or areas you'd like to focus on..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-calendar-check me-2"></i>Book Session
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Available Coaches -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Available Coaches</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($availableCoaches)): ?>
                            <div class="row">
                                <?php foreach ($availableCoaches as $coach): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100 coach-card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="me-3">
                                                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($coach['first_name'] . ' ' . $coach['last_name']); ?></h6>
                                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($coach['specialization'] ?? 'General Fitness'); ?></p>
                                                        <div>
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?php echo $i <= $coach['rating'] ? 'text-warning' : 'text-muted'; ?> small"></i>
                                                            <?php endfor; ?>
                                                            <small class="text-muted">(<?php echo number_format($coach['rating'], 1); ?>)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="coach-details">
                                                    <p class="small text-muted mb-2">
                                                        <i class="fas fa-briefcase me-1"></i><?php echo $coach['experience_years']; ?> years experience
                                                    </p>
                                                    <?php if ($coach['phone']): ?>
                                                        <p class="small text-muted mb-2">
                                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($coach['phone']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="selectCoach(<?php echo $coach['coach_id']; ?>)">
                                                            <i class="fas fa-check me-1"></i>Select
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewCoachProfile(<?php echo $coach['coach_id']; ?>)">
                                                            <i class="fas fa-info me-1"></i>Profile
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                                <h5>No coaches available</h5>
                                <p class="text-muted">Check back later for available coaching sessions</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Sessions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Your Upcoming Sessions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($upcomingSessions)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Coach</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcomingSessions as $session): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($session['coach_first_name'] . ' ' . $session['coach_last_name']); ?>
                                                </td>
                                                <td><?php echo formatDate($session['session_date']); ?></td>
                                                <td><?php echo formatTime($session['session_time']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($session['status'] === 'confirmed') ? 'success' : (($session['status'] === 'pending') ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($session['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="rescheduleSession(<?php echo $session['session_id']; ?>)">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="cancelSession(<?php echo $session['session_id']; ?>)">
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
                                <i class="fas fa-calendar fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No upcoming sessions scheduled</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Session History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($sessionHistory)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Coach</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessionHistory as $session): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($session['coach_first_name'] . ' ' . $session['coach_last_name']); ?>
                                                </td>
                                                <td><?php echo formatDate($session['session_date']); ?></td>
                                                <td><?php echo formatTime($session['session_time']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($session['status'] === 'completed') ? 'success' : (($session['status'] === 'confirmed') ? 'info' : (($session['status'] === 'pending') ? 'warning' : 'danger')); ?>">
                                                        <?php echo ucfirst($session['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($session['status'] === 'completed'): ?>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="rateCoach(<?php echo $session['session_id']; ?>)">
                                                            <i class="fas fa-star me-1"></i>Rate Coach
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($session['notes']): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars(substr($session['notes'], 0, 50)) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No session history available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select coach function
        function selectCoach(coachId) {
            document.getElementById('coach_id').value = coachId;
            document.getElementById('coach_id').focus();
            
            // Scroll to booking form
            document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
        }

        // View coach profile (placeholder)
        function viewCoachProfile(coachId) {
            // Implement coach profile view
            alert('Coach profile view coming soon!');
        }

        // Reschedule session (placeholder)
        function rescheduleSession(sessionId) {
            // Implement reschedule functionality
            alert('Reschedule functionality coming soon!');
        }

        // Cancel session
        function cancelSession(sessionId) {
            if (confirm('Are you sure you want to cancel this session?')) {
                // Implement cancel functionality
                fetch(`cancel_session.php?id=${sessionId}`, {
                    method: 'DELETE'
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

        // Rate coach (placeholder)
        function rateCoach(sessionId) {
            // Implement rating functionality
            alert('Coach rating functionality coming soon!');
        }

        // Set minimum date to today
        document.getElementById('session_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
