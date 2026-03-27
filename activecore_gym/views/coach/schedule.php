<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Coach.php';
require_once __DIR__ . '/../../models/Session.php';

// Check if user is logged in and is a coach
checkAccess('coach');

$coach = new Coach();
$session = new Session();

$coachId = $_SESSION['coach_id'];

// Get date parameters
$view = $_GET['view'] ?? 'week';
$currentDate = $_GET['date'] ?? date('Y-m-d');

// Calculate date range based on view
if ($view === 'day') {
    $startDate = $currentDate;
    $endDate = $currentDate;
    $prevDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
    $nextDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
} elseif ($view === 'week') {
    $startOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($currentDate)));
    $endOfWeek = date('Y-m-d', strtotime('saturday this week', strtotime($currentDate)));
    $startDate = $startOfWeek;
    $endDate = $endOfWeek;
    $prevDate = date('Y-m-d', strtotime($currentDate . ' -1 week'));
    $nextDate = date('Y-m-d', strtotime($currentDate . ' +1 week'));
} else { // month
    $startDate = date('Y-m-01', strtotime($currentDate));
    $endDate = date('Y-m-t', strtotime($currentDate));
    $prevDate = date('Y-m-d', strtotime($currentDate . ' -1 month'));
    $nextDate = date('Y-m-d', strtotime($currentDate . ' +1 month'));
}

// Get sessions for the date range
$schedule = $coach->getWeeklySchedule($coachId, $startDate, $endDate);

// Organize sessions by date
$sessionsByDate = [];
foreach ($schedule as $session) {
    $sessionsByDate[$session['session_date']][] = $session;
}

// Generate calendar dates for display
$calendarDates = [];
$current = new DateTime($startDate);
$end = new DateTime($endDate);

while ($current <= $end) {
    $calendarDates[] = $current->format('Y-m-d');
    $current->modify('+1 day');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - ActiveCore Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>css/style.css" rel="stylesheet">
    <style>
        .calendar-day {
            min-height: 120px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: white;
        }
        .calendar-day.has-sessions {
            background: #f8f9fa;
        }
        .calendar-day.today {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        .session-item {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        .session-item.pending {
            background: #ffc107;
            color: #212529;
        }
        .session-item.confirmed {
            background: #28a745;
        }
        .session-item.completed {
            background: #6c757d;
        }
        .session-item.cancelled {
            background: #dc3545;
        }
        .time-slot {
            border-left: 3px solid #007bff;
            padding-left: 10px;
            margin-bottom: 8px;
        }
        .view-toggle .btn {
            border-radius: 0;
        }
        .view-toggle .btn:first-child {
            border-radius: 0.375rem 0 0 0.375rem;
        }
        .view-toggle .btn:last-child {
            border-radius: 0 0.375rem 0.375rem 0;
        }
    </style>
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
                        <a class="nav-link" href="sessions.php">Sessions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="schedule.php">Schedule</a>
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
                <h1><i class="fas fa-calendar me-2"></i>Coach Schedule</h1>
                <p class="text-muted">View and manage your training schedule</p>
            </div>
        </div>

        <!-- View Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="btn-group view-toggle" role="group">
                                    <a href="?view=day&date=<?php echo $currentDate; ?>" class="btn <?php echo $view === 'day' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Day
                                    </a>
                                    <a href="?view=week&date=<?php echo $currentDate; ?>" class="btn <?php echo $view === 'week' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Week
                                    </a>
                                    <a href="?view=month&date=<?php echo $currentDate; ?>" class="btn <?php echo $view === 'month' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Month
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="?view=<?php echo $view; ?>&date=<?php echo $prevDate; ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                    <span class="btn btn-secondary" disabled>
                                        <?php echo getDisplayDateRange($startDate, $endDate, $view); ?>
                                    </span>
                                    <a href="?view=<?php echo $view; ?>&date=<?php echo $nextDate; ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="?view=<?php echo $view; ?>&date=<?php echo date('Y-m-d'); ?>" class="btn btn-primary">
                                    <i class="fas fa-calendar-day me-2"></i>Today
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Display -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?php echo getDisplayTitle($view, $startDate, $endDate); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($view === 'day'): ?>
                            <!-- Day View -->
                            <?php
                            $daySessions = $sessionsByDate[$currentDate] ?? [];
                            $timeSlots = generateTimeSlots($daySessions);
                            ?>
                            
                            <?php if (!empty($timeSlots)): ?>
                                <div class="timeline">
                                    <?php foreach ($timeSlots as $time => $sessions): ?>
                                        <div class="time-slot">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6><?php echo formatTime($time); ?></h6>
                                                    <?php foreach ($sessions as $session): ?>
                                                        <div class="session-item <?php echo $session['status']; ?> mb-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span>
                                                                    <?php echo htmlspecialchars($session['member_first_name'] . ' ' . $session['member_last_name']); ?>
                                                                </span>
                                                                <span class="badge bg-light text-dark">
                                                                    <?php echo ucfirst($session['status']); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar-day fa-3x text-muted mb-3"></i>
                                    <h5>No sessions scheduled</h5>
                                    <p class="text-muted">You have no sessions on this date</p>
                                </div>
                            <?php endif; ?>

                        <?php elseif ($view === 'week'): ?>
                            <!-- Week View -->
                            <div class="row">
                                <?php foreach ($calendarDates as $date): ?>
                                    <div class="col-md-1 col-sm-6 mb-3">
                                        <div class="calendar-day <?php echo ($date === date('Y-m-d')) ? 'today' : ''; ?> <?php echo (isset($sessionsByDate[$date])) ? 'has-sessions' : ''; ?>">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong><?php echo date('D', strtotime($date)); ?></strong>
                                                <small><?php echo date('j', strtotime($date)); ?></small>
                                            </div>
                                            
                                            <?php if (isset($sessionsByDate[$date])): ?>
                                                <?php foreach ($sessionsByDate[$date] as $session): ?>
                                                    <div class="session-item <?php echo $session['status']; ?> mb-1" 
                                                         onclick="viewSessionDetails(<?php echo $session['session_id']; ?>)">
                                                        <div class="small">
                                                            <?php echo formatTime($session['session_time']); ?>
                                                        </div>
                                                        <div class="small">
                                                            <?php echo htmlspecialchars($session['member_first_name']); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php else: ?>
                            <!-- Month View -->
                            <div class="row">
                                <?php foreach ($calendarDates as $date): ?>
                                    <div class="col-md-2 col-sm-4 col-6 mb-3">
                                        <div class="calendar-day <?php echo ($date === date('Y-m-d')) ? 'today' : ''; ?> <?php echo (isset($sessionsByDate[$date])) ? 'has-sessions' : ''; ?>">
                                            <div class="text-center mb-2">
                                                <strong><?php echo date('j', strtotime($date)); ?></strong>
                                                <br><small><?php echo date('D', strtotime($date)); ?></small>
                                            </div>
                                            
                                            <?php if (isset($sessionsByDate[$date])): ?>
                                                <div class="text-center">
                                                    <small class="badge bg-primary">
                                                        <?php echo count($sessionsByDate[$date]); ?> sessions
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Sessions Summary -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Upcoming Sessions</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $upcomingSessions = $coach->getUpcomingSessions($coachId);
                        if (!empty($upcomingSessions)):
                        ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Member</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcomingSessions as $session): ?>
                                            <tr>
                                                <td><?php echo formatDate($session['session_date']); ?></td>
                                                <td><?php echo formatTime($session['session_time']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($session['member_first_name'] . ' ' . $session['member_last_name']); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo getSessionStatusColor($session['status']); ?>">
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
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewSessionDetails(<?php echo $session['session_id']; ?>)">
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
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-alt fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No upcoming sessions</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Details Modal -->
    <div class="modal fade" id="sessionModal" tabindex="-1">
        <div class="modal-dialog">
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

        function viewSessionDetails(sessionId) {
            fetch(`get_session_details.php?id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('sessionModalContent').innerHTML = `
                        <div class="row">
                            <div class="col-12">
                                <h6>Session Information</h6>
                                <p><strong>Session ID:</strong> #${data.session_id}</p>
                                <p><strong>Date:</strong> ${new Date(data.session_date).toLocaleDateString()}</p>
                                <p><strong>Time:</strong> ${new Date('1970-01-01T' + data.session_time).toLocaleTimeString()}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${getSessionStatusColor(data.status)}">${data.status}</span></p>
                                <p><strong>Member:</strong> ${data.member_first_name} ${data.member_last_name}</p>
                                ${data.notes ? `<p><strong>Notes:</strong> ${data.notes}</p>` : ''}
                            </div>
                        </div>
                    `;
                    
                    new bootstrap.Modal(document.getElementById('sessionModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading session details');
                });
        }

        function confirmSession(sessionId) {
            if (confirm('Are you sure you want to confirm this session?')) {
                fetch('sessions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=confirm&session_id=${sessionId}`
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
                fetch('sessions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=complete&session_id=${sessionId}`
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
    </script>

    <?php
    // Helper functions for the view
    function getDisplayDateRange($startDate, $endDate, $view) {
        if ($view === 'day') {
            return date('F j, Y', strtotime($startDate));
        } elseif ($view === 'week') {
            return date('M j', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate));
        } else {
            return date('F Y', strtotime($startDate));
        }
    }

    function getDisplayTitle($view, $startDate, $endDate) {
        if ($view === 'day') {
            return 'Daily Schedule - ' . date('l, F j, Y', strtotime($startDate));
        } elseif ($view === 'week') {
            return 'Weekly Schedule - ' . date('M j', strtotime($startDate)) . ' to ' . date('M j, Y', strtotime($endDate));
        } else {
            return 'Monthly Schedule - ' . date('F Y', strtotime($startDate));
        }
    }

    function generateTimeSlots($sessions) {
        $timeSlots = [];
        $times = [
            '06:00:00', '07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00',
            '12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00',
            '18:00:00', '19:00:00', '20:00:00'
        ];

        foreach ($times as $time) {
            $timeSlots[$time] = [];
        }

        foreach ($sessions as $session) {
            $timeSlots[$session['session_time']][] = $session;
        }

        return $timeSlots;
    }
    ?>
</body>
</html>
