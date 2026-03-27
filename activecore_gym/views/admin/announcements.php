<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Announcement.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Set page variables for header
$page_title = 'Announcements Management';
$current_page = 'announcements';

$announcement = new Announcement();

// Handle simple form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $announcement->title = sanitizeInput($_POST['title'] ?? '');
        $announcement->content = sanitizeInput($_POST['content'] ?? '');
        $announcement->announcement_type = sanitizeInput($_POST['announcement_type'] ?? 'general');
        $announcement->priority = sanitizeInput($_POST['priority'] ?? 'medium');
        $announcement->expires_at = sanitizeInput($_POST['expires_at'] ?? '');
        $announcement->is_active = true;
        $announcement->created_by = $_SESSION['user_id'];
        
        if ($announcement->create()) {
            echo "<script>alert('Announcement created successfully!'); window.location.href='announcements_simple.php';</script>";
        } else {
            echo "<script>alert('Failed to create announcement'); window.location.href='announcements_simple.php';</script>";
        }
        exit;
    }
    
    if ($action === 'archive_expired') {
        $announcement->deactivateExpired();
        echo "<script>alert('Archived expired announcements successfully!'); window.location.href='announcements_simple.php';</script>";
        exit;
    }
    
    if ($action === 'toggle_status') {
        $announcementId = intval($_POST['announcement_id'] ?? 0);
        if ($announcementId > 0) {
            $announcement->getById($announcementId);
            if ($announcement->announcement_id) {
                $announcement->is_active = !$announcement->is_active;
                $announcement->update();
                echo "<script>alert('Announcement status updated successfully!'); window.location.href='announcements_simple.php';</script>";
                exit;
            }
        }
    }
}

// Get data
$allAnnouncements = $announcement->getAll();
$announcementStats = $announcement->getStats();

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-bullhorn me-2"></i>Announcements Management</h2>
        <p class="text-muted">Create and manage gym announcements</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?php echo $announcementStats['total']; ?></h3>
                <p class="mb-0">Total</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3><?php echo $announcementStats['active']; ?></h3>
                <p class="mb-0">Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3><?php echo $announcementStats['high_priority_count']; ?></h3>
                <p class="mb-0">High Priority</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3><?php echo $announcementStats['expired']; ?></h3>
                <p class="mb-0">Expired</p>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fas fa-plus me-2"></i>Create New Announcement
                </button>
                <button class="btn btn-outline-warning ms-2" onclick="archiveExpired()">
                    <i class="fas fa-archive me-2"></i>Archive Expired
                </button>
                <a href="announcements_simple.php?action=export_announcements" class="btn btn-outline-info ms-2">
                    <i class="fas fa-download me-1"></i>Export
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Announcements Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Announcements</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($allAnnouncements)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Expires</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allAnnouncements as $ann): ?>
                                    <tr>
                                        <td>#<?php echo $ann['announcement_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($ann['title']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($ann['content'], 0, 50)) . '...'; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst($ann['announcement_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $colors = ['high' => 'danger', 'medium' => 'warning', 'low' => 'info'];
                                            ?>
                                            <span class="badge bg-<?php echo $colors[$ann['priority']]; ?>">
                                                <?php echo ucfirst($ann['priority']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $ann['is_active'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $ann['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($ann['created_at']); ?></td>
                                        <td>
                                            <?php 
                                            $isExpired = $ann['expires_at'] && $ann['expires_at'] < date('Y-m-d');
                                            echo $ann['expires_at'] ? 
                                                ($isExpired ? '<span class="text-danger">' . formatDate($ann['expires_at']) . ' (Expired)</span>' : formatDate($ann['expires_at'])) : 
                                                'No expiry';
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="announcement_id" value="<?php echo $ann['announcement_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-<?php echo $ann['is_active'] ? 'warning' : 'success'; ?>" 
                                                            title="<?php echo $ann['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                        <i class="fas fa-<?php echo $ann['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                </form>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewDetails(<?php echo $ann['announcement_id']; ?>)">
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
                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No announcements found</h5>
                        <p class="text-muted">Create your first announcement to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="announcements_simple.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="announcement_type" class="form-label">Type</label>
                            <select class="form-select" id="announcement_type" name="announcement_type">
                                <option value="general">General</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="promotion">Promotion</option>
                                <option value="event">Event</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expiry Date (Optional)</label>
                        <input type="date" class="form-control" id="expires_at" name="expires_at">
                        <small class="text-muted">Leave blank for no expiry</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Announcement Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(id) {
    // Find the announcement data from the table
    const rows = document.querySelectorAll('tbody tr');
    let announcement = null;
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells[0] && cells[0].textContent === '#' + id) {
            announcement = {
                id: id,
                title: cells[1].querySelector('strong').textContent,
                content: cells[1].querySelector('small').textContent.replace('...', ''),
                type: cells[2].querySelector('.badge').textContent,
                priority: cells[3].querySelector('.badge').textContent,
                status: cells[4].querySelector('.badge').textContent,
                created: cells[5].textContent,
                expires: cells[6].textContent
            };
        }
    });
    
    if (announcement) {
        document.getElementById('viewContent').innerHTML = `
            <div class="row">
                <div class="col-md-8">
                    <h4>${announcement.title}</h4>
                    <p>${announcement.content}</p>
                </div>
                <div class="col-md-4">
                    <h6>Details</h6>
                    <p><strong>Type:</strong> <span class="badge bg-info">${announcement.type}</span></p>
                    <p><strong>Priority:</strong> <span class="badge bg-warning">${announcement.priority}</span></p>
                    <p><strong>Status:</strong> <span class="badge bg-success">${announcement.status}</span></p>
                    <p><strong>Created:</strong> ${announcement.created}</p>
                    <p><strong>Expires:</strong> ${announcement.expires}</p>
                </div>
            </div>
        `;
        
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    }
}

function archiveExpired() {
    if (confirm('Are you sure you want to archive all expired announcements?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'announcements_simple.php';
        form.innerHTML = '<input type="hidden" name="action" value="archive_expired">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
// Handle export
if (isset($_GET['action']) && $_GET['action'] === 'export_announcements') {
    $announcements = $announcement->getAll();
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="announcements_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Content', 'Type', 'Priority', 'Status', 'Created', 'Expires']);
    
    foreach ($announcements as $row) {
        fputcsv($output, [
            $row['announcement_id'],
            $row['title'],
            strip_tags($row['content']),
            $row['announcement_type'],
            $row['priority'],
            $row['is_active'] ? 'Active' : 'Inactive',
            $row['created_at'],
            $row['expires_at'] ?: 'Never'
        ]);
    }
    
    fclose($output);
    exit;
}

// Include footer
require_once __DIR__ . '/footer.php';
?>
