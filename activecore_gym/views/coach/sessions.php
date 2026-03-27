<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Session.php';
require_once __DIR__ . '/../../models/Coach.php';

// Check if user is logged in and is a coach
checkAccess('coach');

$session = new Session();
$coach = new Coach();

$coachId = $_SESSION['coach_id'];
$success = '';
$error = '';

// Handle session actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $sessionId = intval($_POST['session_id'] ?? 0);
    
    if ($sessionId <= 0) {
        $error = 'Invalid session ID';
    } else {
        switch ($_POST['action']) {
            case 'confirm':
                if ($session->getById($sessionId)) {
                    $session->updateStatus('confirmed');
                    $_SESSION['success'] = 'Session confirmed successfully!';
                }
                break;
            case 'complete':
                if ($session->getById($sessionId)) {
                    $session->updateStatus('completed');
                    $_SESSION['success'] = 'Session marked as completed!';
                }
                break;
            case 'cancel':
                if ($session->getById($sessionId)) {
                    $session->updateStatus('cancelled');
                    $_SESSION['success'] = 'Session cancelled!';
                }
                break;
        }
        header('Location: sessions.php');
        exit();
    }
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d', strtotime('+30 days'));

// Get sessions based on filters
if ($status) {
    $sessions = $session->getByStatus($status);
} else {
    $sessions = $session->getByCoach($coachId, 50);
}

// Filter sessions by date range if specified
if ($date_from && $date_to) {
    $sessions = array_filter($sessions, function($s) use ($date_from, $date_to) {
        return $s['session_date'] >= $date_from && $s['session_date'] <= $date_to;
    });
}

// Get coach statistics
$stats = $coach->getStats($coachId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessions Management - ActiveCore Gym</title>
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
                        <a class="nav-link active" href="sessions.php">Sessions</a>
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

        <div class="row mb-4">
            <div class="col-12">
                <h1><i class="fas fa-calendar-alt me-2"></i>Sessions Management</h1>
                <p class="text-muted">Manage your training sessions and member appointments</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['total_sessions']; ?></h3>
                        <p class="mb-0">Total Sessions</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['pending_sessions']; ?></h3>
                        <p class="mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['confirmed_sessions']; ?></h3>
                        <p class="mb-0">Confirmed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['completed_sessions']; ?></h3>
                        <p class="mb-0">Completed</p>
                    </div>
                </div>
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
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="<?php echo htmlspecialchars($date_from); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="<?php echo htmlspecialchars($date_to); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-2"></i>Filter
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
                    <a href="?status=pending" class="btn <?php echo $status === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        <i class="fas fa-clock me-2"></i>Pending
                    </a>
                    <a href="?status=confirmed" class="btn <?php echo $status === 'confirmed' ? 'btn-info' : 'btn-outline-info'; ?>">
                        <i class="fas fa-check me-2"></i>Confirmed
                    </a>
                    <a href="?status=completed" class="btn <?php echo $status === 'completed' ? 'btn-success' : 'btn-outline-success'; ?>">
                        <i class="fas fa-check-double me-2"></i>Completed
                    </a>
                    <a href="?" class="btn <?php echo (!$status) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-list me-2"></i>All Sessions
                    </a>
                </div>
            </div>
        </div>

        <!-- Sessions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Sessions List</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-success" onclick="exportSessions()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($sessions)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="sessionsTable">
                                    <thead>
                                        <tr>
                                            <th>Session ID</th>
                                            <th>Member</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sessions as $session): ?>
                                            <tr>
                                                <td>#<?php echo $session['session_id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($session['member_first_name'] . ' ' . $session['member_last_name']); ?>
                                                </td>
                                                <td><?php echo formatDate($session['session_date']); ?></td>
                                                <td><?php echo formatTime($session['session_time']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getSessionStatusColor($session['status']); ?>">
                                                        <?php echo ucfirst($session['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($session['notes']): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars(substr($session['notes'], 0, 30)) . '...'; ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($session['status'] === 'pending'): ?>
                                                            <button class="btn btn-sm btn-success" onclick="updateSessionStatus(<?php echo $session['session_id']; ?>, 'confirm')" title="Confirm">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($session['status'] === 'confirmed'): ?>
                                                            <button class="btn btn-sm btn-primary" onclick="updateSessionStatus(<?php echo $session['session_id']; ?>, 'complete')" title="Complete">
                                                                <i class="fas fa-check-double"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewSessionDetails(<?php echo $session['session_id']; ?>)" title="View Details">
                                                            <i class="fas fa-info-circle"></i>
                                                        </button>
                                                        <?php if ($session['status'] !== 'completed'): ?>
                                                            <button class="btn btn-sm btn-outline-danger" onclick="updateSessionStatus(<?php echo $session['session_id']; ?>, 'cancel')" title="Cancel">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                <h4>No sessions found</h4>
                                <p class="text-muted">No sessions match your current filters</p>
                                <a href="sessions.php" class="btn btn-primary">View All Sessions</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Details Modal -->
    <div class="modal fade" id="sessionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Session Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="sessionModalContent">
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
        function getSessionStatusColor(status) {
            switch(status) {
                case 'pending': return 'warning';
                case 'confirmed': return 'info';
                case 'completed': return 'success';
                case 'cancelled': return 'danger';
                default: return 'secondary';
            }
        }

        function updateSessionStatus(sessionId, action) {
            const actionText = action === 'confirm' ? 'confirm' : (action === 'complete' ? 'mark as completed' : 'cancel');
            
            if (confirm(`Are you sure you want to ${actionText} this session?`)) {
                fetch('sessions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=${action}&session_id=${sessionId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to update session');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating session');
                });
            }
        }

        function viewSessionDetails(sessionId) {
            fetch(`get_session_details.php?id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('sessionModalContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Session Information</h6>
                                <p><strong>Session ID:</strong> #${data.session_id}</p>
                                <p><strong>Date:</strong> ${new Date(data.session_date).toLocaleDateString()}</p>
                                <p><strong>Time:</strong> ${new Date('1970-01-01T' + data.session_time).toLocaleTimeString()}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${getSessionStatusColor(data.status)}">${data.status}</span></p>
                                <p><strong>Created:</strong> ${new Date(data.created_at).toLocaleDateString()}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Member Information</h6>
                                <p><strong>Name:</strong> ${data.member_first_name} ${data.member_last_name}</p>
                                <p><strong>Member ID:</strong> #${data.member_id}</p>
                            </div>
                        </div>
                        ${data.notes ? `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Notes</h6>
                                    <p>${data.notes}</p>
                                </div>
                            </div>
                        ` : ''}
                    `;
                    
                    new bootstrap.Modal(document.getElementById('sessionModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading session details');
                });
        }

        function exportSessions() {
            // Implement export functionality
            alert('Export functionality coming soon!');
        }
    </script>
</body>
</html>
