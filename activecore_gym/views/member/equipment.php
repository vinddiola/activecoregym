<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Equipment.php';

// Check if user is logged in and is a member
checkAccess('member');

$equipment = new Equipment();

// Get filter parameters
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Get equipment based on filters
if ($category) {
    $equipmentList = $equipment->getByCategory($category);
} elseif ($status) {
    $equipmentList = $equipment->getByStatus($status);
} elseif ($search) {
    $equipmentList = $equipment->search($search);
} else {
    $equipmentList = $equipment->getAll(50, 0);
}

// Get categories for filter
$categories = $equipment->getCategories();

// Get equipment statistics
$stats = $equipment->getStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment - ActiveCore Gym</title>
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
                        <a class="nav-link active" href="equipment.php">Equipment</a>
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
                <h1><i class="fas fa-tools me-2"></i>Gym Equipment</h1>
                <p class="text-muted">Browse our state-of-the-art fitness equipment and check availability</p>
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
                            foreach ($stats as $stat) {
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
                            foreach ($stats as $stat) {
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
                            foreach ($stats as $stat) {
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
                            foreach ($stats as $stat) {
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

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($cat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="in_use" <?php echo $status === 'in_use' ? 'selected' : ''; ?>>In Use</option>
                                        <option value="maintenance" <?php echo $status === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        <option value="out_of_order" <?php echo $status === 'out_of_order' ? 'selected' : ''; ?>>Out of Order</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="search" class="form-label">Search Equipment</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or description...">
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
                    <a href="?status=available" class="btn <?php echo $status === 'available' ? 'btn-success' : 'btn-outline-success'; ?>">
                        <i class="fas fa-check me-2"></i>Available
                    </a>
                    <a href="?category=cardio" class="btn <?php echo $category === 'cardio' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-running me-2"></i>Cardio
                    </a>
                    <a href="?category=strength" class="btn <?php echo $category === 'strength' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-dumbbell me-2"></i>Strength
                    </a>
                    <a href="?category=flexibility" class="btn <?php echo $category === 'flexibility' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-child me-2"></i>Flexibility
                    </a>
                    <a href="?" class="btn <?php echo (!$category && !$status && !$search) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-list me-2"></i>All Equipment
                    </a>
                </div>
            </div>
        </div>

        <!-- Equipment Grid -->
        <div class="row">
            <?php if (!empty($equipmentList)): ?>
                <?php foreach ($equipmentList as $item): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 equipment-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <span class="badge bg-<?php echo getEquipmentStatusColor($item['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($item['status'])); ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="badge bg-secondary me-2">
                                        <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($item['category']); ?>
                                    </span>
                                </div>

                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 120)) . '...'; ?>
                                </p>

                                <div class="equipment-details mb-3">
                                    <?php if ($item['purchase_date']): ?>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-calendar-plus me-1"></i>Purchased: <?php echo formatDate($item['purchase_date']); ?>
                                        </small>
                                    <?php endif; ?>
                                    
                                    <?php if ($item['last_maintenance']): ?>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-wrench me-1"></i>Last Maintenance: <?php echo formatDate($item['last_maintenance']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-2">
                                    <?php if ($item['status'] === 'available'): ?>
                                        <button class="btn btn-success btn-sm" onclick="useEquipment(<?php echo $item['equipment_id']; ?>)">
                                            <i class="fas fa-play me-1"></i>Use Now
                                        </button>
                                    <?php elseif ($item['status'] === 'in_use'): ?>
                                        <button class="btn btn-warning btn-sm" disabled>
                                            <i class="fas fa-hourglass-half me-1"></i>In Use
                                        </button>
                                    <?php elseif ($item['status'] === 'maintenance'): ?>
                                        <button class="btn btn-info btn-sm" disabled>
                                            <i class="fas fa-wrench me-1"></i>Maintenance
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-danger btn-sm" disabled>
                                            <i class="fas fa-times me-1"></i>Out of Order
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewEquipmentDetails(<?php echo $item['equipment_id']; ?>)">
                                        <i class="fas fa-info-circle me-1"></i>Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <h4>No equipment found</h4>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                        <a href="?" class="btn btn-primary">View All Equipment</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Equipment Details Modal -->
    <div class="modal fade" id="equipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEquipmentTitle">Equipment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalEquipmentContent">
                    <!-- Content will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="modalUseEquipment">
                        <i class="fas fa-play me-1"></i>Use Equipment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentEquipmentId = null;

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
            currentEquipmentId = equipmentId;
            
            // Fetch equipment details via AJAX
            fetch(`get_equipment_details.php?id=${equipmentId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalEquipmentTitle').textContent = data.name;
                    document.getElementById('modalEquipmentContent').innerHTML = `
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Description</h6>
                                <p>${data.description || 'No description available'}</p>
                                
                                <h6 class="mt-3">Category</h6>
                                <p>${data.category}</p>
                                
                                <h6 class="mt-3">Status</h6>
                                <span class="badge bg-${getEquipmentStatusColor(data.status)}">${data.status}</span>
                                
                                ${data.purchase_date ? `
                                    <h6 class="mt-3">Purchase Date</h6>
                                    <p>${new Date(data.purchase_date).toLocaleDateString()}</p>
                                ` : ''}
                                
                                ${data.last_maintenance ? `
                                    <h6 class="mt-3">Last Maintenance</h6>
                                    <p>${new Date(data.last_maintenance).toLocaleDateString()}</p>
                                ` : ''}
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                                    <div>
                                        <span class="badge bg-${getEquipmentStatusColor(data.status)} fs-6">
                                            ${data.status}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    const useButton = document.getElementById('modalUseEquipment');
                    useButton.style.display = data.status === 'available' ? 'block' : 'none';
                    useButton.onclick = function() {
                        useEquipment(equipmentId);
                        bootstrap.Modal.getInstance(document.getElementById('equipmentModal')).hide();
                    };
                    
                    new bootstrap.Modal(document.getElementById('equipmentModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading equipment details');
                });
        }

        function useEquipment(equipmentId) {
            // Implement equipment usage tracking
            alert('Equipment usage tracking coming soon! This would mark the equipment as "in use".');
        }
    </script>
</body>
</html>
