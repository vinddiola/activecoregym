<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Member.php';
require_once __DIR__ . '/../../models/User.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Set page variables for header
$page_title = 'Members Management';
$current_page = 'members';

$member = new Member();
$user = new User();

// Handle member actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'deactivate':
            $memberId = intval($_POST['member_id'] ?? 0);
            if ($memberId > 0) {
                $memberData = $member->getById($memberId);
                if ($memberData) {
                    $user->user_id = $memberData['user_id'];
                    $user->deactivate();
                    $_SESSION['success'] = 'Member deactivated successfully';
                }
            }
            break;
        case 'activate':
            $memberId = intval($_POST['member_id'] ?? 0);
            if ($memberId > 0) {
                $memberData = $member->getById($memberId);
                if ($memberData) {
                    $user->user_id = $memberData['user_id'];
                    $user->activate();
                    $_SESSION['success'] = 'Member activated successfully';
                }
            }
            break;
    }
    header('Location: members.php');
    exit();
}

// Get all members
$members = $member->getAll();

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-users me-2"></i>Members Management</h2>
        <p class="text-muted">Manage gym members and their accounts</p>
    </div>
</div>

        <!-- Members Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Members</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-success" onclick="exportMembers()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($members)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="membersTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Join Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($members as $member): ?>
                                            <tr>
                                                <td>#<?php echo $member['member_id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($member['username']); ?></td>
                                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                <td><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></td>
                                                <td><?php echo formatDate($member['membership_date']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($member['membership_status'] === 'active' && $member['is_active']) ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($member['membership_status']); ?>
                                                        <?php echo (!$member['is_active']) ? '(Inactive)' : ''; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($member['is_active']): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="deactivate">
                                                                <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Are you sure you want to deactivate this member?')">
                                                                    <i class="fas fa-pause"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="activate">
                                                                <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Are you sure you want to activate this member?')">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewMemberDetails(<?php echo $member['member_id']; ?>)">
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
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h4>No members found</h4>
                                <p class="text-muted">No members have registered yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Details Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Member Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="memberModalContent">
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
        function viewMemberDetails(memberId) {
            fetch(`get_member_details.php?id=${memberId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('memberModalContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Personal Information</h6>
                                <p><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                                <p><strong>Username:</strong> ${data.username}</p>
                                <p><strong>Email:</strong> ${data.email}</p>
                                <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Membership Details</h6>
                                <p><strong>Member ID:</strong> #${data.member_id}</p>
                                <p><strong>Join Date:</strong> ${new Date(data.membership_date).toLocaleDateString()}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${data.membership_status === 'active' ? 'success' : 'danger'}">${data.membership_status}</span></p>
                                <p><strong>Account:</strong> <span class="badge bg-${data.is_active ? 'success' : 'danger'}">${data.is_active ? 'Active' : 'Inactive'}</span></p>
                            </div>
                        </div>
                    `;
                    
                    new bootstrap.Modal(document.getElementById('memberModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading member details');
                });
        }

        function exportMembers() {
            alert('Export functionality coming soon!');
        }
    </script>

<?php
// Include footer
require_once __DIR__ . '/footer.php';
?>
