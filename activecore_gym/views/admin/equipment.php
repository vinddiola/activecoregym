<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Equipment.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Set page variables for header
$page_title = 'Equipment Management';
$current_page = 'equipment';

$equipment = new Equipment();

// Handle equipment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $equipment->name = sanitizeInput($_POST['name'] ?? '');
            $equipment->category = sanitizeInput($_POST['category'] ?? '');
            $equipment->status = sanitizeInput($_POST['status'] ?? 'available');
            $equipment->purchase_date = sanitizeInput($_POST['purchase_date'] ?? date('Y-m-d'));
            $equipment->last_maintenance = sanitizeInput($_POST['last_maintenance'] ?? '');
            $equipment->description = sanitizeInput($_POST['description'] ?? '');
            
            if (empty($equipment->name) || empty($equipment->category)) {
                $_SESSION['error'] = 'Equipment name and category are required';
            } else {
                try {
                    if ($equipment->create()) {
                        $_SESSION['success'] = 'Equipment added successfully';
                    } else {
                        $_SESSION['error'] = 'Failed to add equipment - please check all fields';
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
                }
            }
            break;
            
        case 'update_status':
            $equipmentId = intval($_POST['equipment_id'] ?? 0);
            $newStatus = sanitizeInput($_POST['new_status'] ?? '');
            
            if ($equipmentId > 0 && $newStatus) {
                try {
                    $equipment->equipment_id = $equipmentId;
                    if ($equipment->updateStatus($newStatus)) {
                        $_SESSION['success'] = 'Equipment status updated successfully';
                    } else {
                        $_SESSION['error'] = 'Failed to update equipment status';
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
                }
            }
            break;
    }
    header('Location: equipment.php');
    exit();
}

// Get all equipment
$allEquipment = $equipment->getAll();
$maintenanceNeeded = $equipment->getMaintenanceNeeded();
$equipmentStats = $equipment->getStats();
$categories = $equipment->getCategories();

// Include header
require_once __DIR__ . '/header.php';
?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-tools me-2"></i>Equipment Management</h2>
        <p class="text-muted">Manage gym equipment and maintenance</p>
    </div>
</div>

<!-- Equipment Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3>
                    <?php 
                    $availableCount = 0;
                    foreach ($equipmentStats as $stat) {
                        if ($stat['status'] === 'available') $availableCount = $stat['count'];
                    }
                    echo $availableCount;
                    ?>
                </h3>
                <p class="mb-0">Available</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3>
                    <?php 
                    $inUseCount = 0;
                    foreach ($equipmentStats as $stat) {
                        if ($stat['status'] === 'in_use') $inUseCount = $stat['count'];
                    }
                    echo $inUseCount;
                    ?>
                </h3>
                <p class="mb-0">In Use</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3>
                    <?php 
                    $maintenanceCount = 0;
                    foreach ($equipmentStats as $stat) {
                        if ($stat['status'] === 'maintenance') $maintenanceCount = $stat['count'];
                    }
                    echo $maintenanceCount;
                    ?>
                </h3>
                <p class="mb-0">Maintenance</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3>
                    <?php 
                    $outOfOrderCount = 0;
                    foreach ($equipmentStats as $stat) {
                        if ($stat['status'] === 'out_of_order') $outOfOrderCount = $stat['count'];
                    }
                    echo $outOfOrderCount;
                    ?>
                </h3>
                <p class="mb-0">Out of Order</p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($maintenanceNeeded)): ?>
    <!-- Maintenance Needed Alert -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Equipment Maintenance Needed</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Category</th>
                                    <th>Last Maintenance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maintenanceNeeded as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td><?php echo $item['last_maintenance'] ? formatDate($item['last_maintenance']) : 'Never'; ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="equipment_id" value="<?php echo $item['equipment_id']; ?>">
                                                <input type="hidden" name="new_status" value="available">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this equipment as available?')">
                                                    <i class="fas fa-check"></i> Available
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

        <!-- Equipment Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Equipment</h5>
                        <div>
                            <a href="add_equipment.php" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-plus me-1"></i>Add Equipment
                            </a>
                            <button class="btn btn-sm btn-outline-primary" onclick="exportEquipment()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($allEquipment)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" id="equipmentTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Purchase Date</th>
                                            <th>Last Maintenance</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allEquipment as $item): ?>
                                            <tr>
                                                <td>#<?php echo $item['equipment_id']; ?></td>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getEquipmentStatusColor($item['status']); ?>">
                                                        <?php echo ucfirst($item['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $item['purchase_date'] ? formatDate($item['purchase_date']) : 'N/A'; ?></td>
                                                <td><?php echo $item['last_maintenance'] ? formatDate($item['last_maintenance']) : 'Never'; ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm dropdown">
                                                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <form method="POST" style="margin: 0;">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="equipment_id" value="<?php echo $item['equipment_id']; ?>">
                                                                    <input type="hidden" name="new_status" value="available">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-check text-success me-2"></i>Available
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" style="margin: 0;">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="equipment_id" value="<?php echo $item['equipment_id']; ?>">
                                                                    <input type="hidden" name="new_status" value="maintenance">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-wrench text-info me-2"></i>Maintenance
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" style="margin: 0;">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="equipment_id" value="<?php echo $item['equipment_id']; ?>">
                                                                    <input type="hidden" name="new_status" value="out_of_order">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-times text-danger me-2"></i>Out of Order
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewEquipmentDetails(<?php echo $item['equipment_id']; ?>)">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                                <h4>No equipment found</h4>
                                <p class="text-muted">No equipment has been added yet</p>
                                <button class="btn btn-primary" onclick="window.location.href='add_equipment.php'">
                                    <i class="fas fa-plus me-2"></i>Add First Equipment
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Equipment Modal -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Equipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Equipment Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="cardio">Cardio</option>
                                <option value="strength">Strength</option>
                                <option value="flexibility">Flexibility</option>
                                <option value="free_weights">Free Weights</option>
                                <option value="machines">Machines</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="available" selected>Available</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="out_of_order">Out of Order</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="purchase_date" class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_maintenance" class="form-label">Last Maintenance</label>
                                <input type="date" class="form-control" id="last_maintenance" name="last_maintenance">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter equipment description..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Equipment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Equipment Details Modal -->
    <div class="modal fade" id="equipmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Equipment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="equipmentModalContent">
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
        function getEquipmentStatusColor(status) {
            switch(status) {
                case 'available': return 'success';
                case 'in_use': return 'warning';
                case 'maintenance': return 'info';
                case 'out_of_order': return 'danger';
                default: return 'secondary';
            }
        }

        function viewEquipmentDetails(equipmentId) {
            fetch(`get_equipment_details.php?id=${equipmentId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('equipmentModalContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Basic Information</h6>
                                <p><strong>Name:</strong> ${data.name}</p>
                                <p><strong>Category:</strong> ${data.category}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${getEquipmentStatusColor(data.status)}">${data.status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Maintenance Information</h6>
                                <p><strong>Purchase Date:</strong> ${data.purchase_date ? new Date(data.purchase_date).toLocaleDateString() : 'N/A'}</p>
                                <p><strong>Last Maintenance:</strong> ${data.last_maintenance ? new Date(data.last_maintenance).toLocaleDateString() : 'Never'}</p>
                            </div>
                        </div>
                        ${data.description ? `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Description</h6>
                                    <p>${data.description}</p>
                                </div>
                            </div>
                        ` : ''}
                    `;
                    
                    new bootstrap.Modal(document.getElementById('equipmentModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading equipment details');
                });
        }

        function addEquipment() {
            // Show the add equipment modal
            const modal = document.getElementById('addEquipmentModal');
            if (modal) {
                new bootstrap.Modal(modal).show();
            }
        }

        function exportEquipment() {
            alert('Export functionality coming soon!');
        }
    <?php
// Include footer
require_once __DIR__ . '/footer.php';
?>
