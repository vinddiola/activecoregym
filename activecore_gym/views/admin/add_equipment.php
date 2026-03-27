<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Equipment.php';

// Check if user is logged in and is an admin
checkAccess('admin');

// Set page variables for header
$page_title = 'Add Equipment';
$current_page = 'equipment';
$equipment = new Equipment();

// Handle equipment addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment->name = sanitizeInput($_POST['name'] ?? '');
    $equipment->category = sanitizeInput($_POST['category'] ?? '');
    $equipment->status = sanitizeInput($_POST['status'] ?? 'available');
    $equipment->purchase_date = sanitizeInput($_POST['purchase_date'] ?? date('Y-m-d'));
    $equipment->last_maintenance = sanitizeInput($_POST['last_maintenance'] ?? '');
    $equipment->condition = sanitizeInput($_POST['condition'] ?? '');
    $equipment->description = sanitizeInput($_POST['description'] ?? '');
    
    if (empty($equipment->name) || empty($equipment->category)) {
        $_SESSION['error'] = 'Equipment name and category are required';
    } else {
        try {
            if ($equipment->create()) {
                $_SESSION['success'] = 'Equipment added successfully';
                header('Location: equipment.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to add equipment - please check all fields';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Include header
require_once __DIR__ . '/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Equipment - ActiveCore Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CRITICAL: Override ALL Bootstrap text styles for admin pages */
        * {
            color: #FFFFFF !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important;
            font-weight: 600 !important;
        }
        
        .card-header h5,
        .card-body,
        .form-label,
        .form-control,
        .form-select,
        textarea,
        .btn,
        .alert,
        .input-group-text,
        .text-gold,
        .text-light-gold,
        h2, h3, h4, h5, h6,
        p,
        .nav-link,
        .navbar-brand {
            color: #FFFFFF !important;
            text-shadow: 3px 3px 6px rgba(0,0,0,1) !important;
            font-weight: 700 !important;
        }
        
        /* Specific form element fixes */
        .card {
            background: rgba(0,0,0,0.95) !important;
            border: 3px solid #FFD700 !important;
        }
        
        .form-control, .form-select, textarea {
            border: 3px solid #FFD700 !important;
            color: #FFFFFF !important;
            background: rgba(0,0,0,0.8) !important;
            font-weight: 600 !important;
            padding: 12px 20px !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%) !important;
            color: #FFFFFF !important;
            border: none;
            font-weight: 700 !important;
        }
        
        .btn-outline-secondary, .btn-outline-warning {
            color: #FFFFFF !important;
            border-color: #FFD700 !important;
            border-width: 2px !important;
            font-weight: 600 !important;
        }
        
        .input-group-text {
            color: #FFD700 !important;
            font-weight: 600 !important;
        }
        
        .form-label {
            color: #FFD700 !important;
            font-weight: 700 !important;
        }
        
        /* Force visibility for all divs */
        div {
            color: #FFFFFF !important;
        }
    </style>
</head>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="text-gold" style="color: #FFD700 !important; text-shadow: 3px 3px 6px rgba(0,0,0,1) !important; font-weight: 700 !important;"><i class="fas fa-tools me-2" style="color: #FFD700 !important;"></i>Add New Equipment</h2>
        <p class="text-light-gold" style="color: #FFFFFF !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 600 !important;">Add a new equipment item to the gym inventory</p>
    </div>
</div>

<!-- Add Equipment Form -->
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card" style="background: rgba(0,0,0,0.95) !important; border: 3px solid #FFD700 !important;">
            <div class="card-header" style="background: rgba(0,0,0,0.8) !important;">
                <h5 class="mb-0" style="color: #FFD700 !important; text-shadow: 3px 3px 6px rgba(0,0,0,1) !important; font-weight: 700 !important;"><i class="fas fa-plus-circle me-2" style="color: #FFD700 !important;"></i>Equipment Information</h5>
            </div>
            <div class="card-body" style="background: rgba(0,0,0,0.9) !important;">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Equipment Name *</label>
                            <div class="input-group">
                                <span class="input-group-text" style="color: #FFD700 !important; font-weight: 600 !important;">
                                    <i class="fas fa-dumbbell" style="color: #FFD700 !important;"></i>
                                </span>
                                <input type="text" class="form-control" id="name" name="name" 
                                       placeholder="Enter equipment name" required
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                       style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Category *</label>
                            <div class="input-group">
                                <span class="input-group-text" style="color: #FFD700 !important; font-weight: 600 !important;">
                                    <i class="fas fa-tag" style="color: #FFD700 !important;"></i>
                                </span>
                                <select class="form-select" id="category" name="category" required
                                        style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                                    <option value="">Select Category</option>
                                    <option value="Cardio" <?php echo ($_POST['category'] ?? '') === 'Cardio' ? 'selected' : ''; ?>>Cardio</option>
                                    <option value="Strength" <?php echo ($_POST['category'] ?? '') === 'Strength' ? 'selected' : ''; ?>>Strength</option>
                                    <option value="Free Weights" <?php echo ($_POST['category'] ?? '') === 'Free Weights' ? 'selected' : ''; ?>>Free Weights</option>
                                    <option value="Machines" <?php echo ($_POST['category'] ?? '') === 'Machines' ? 'selected' : ''; ?>>Machines</option>
                                    <option value="Functional" <?php echo ($_POST['category'] ?? '') === 'Functional' ? 'selected' : ''; ?>>Functional</option>
                                    <option value="Recovery" <?php echo ($_POST['category'] ?? '') === 'Recovery' ? 'selected' : ''; ?>>Recovery</option>
                                    <option value="Other" <?php echo ($_POST['category'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Status</label>
                            <div class="input-group">
                                <span class="input-group-text" style="color: #FFD700 !important; font-weight: 600 !important;">
                                    <i class="fas fa-info-circle" style="color: #FFD700 !important;"></i>
                                </span>
                                <select class="form-select" id="status" name="status"
                                        style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                                    <option value="available" <?php echo ($_POST['status'] ?? 'available') === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="in_use" <?php echo ($_POST['status'] ?? '') === 'in_use' ? 'selected' : ''; ?>>In Use</option>
                                    <option value="maintenance" <?php echo ($_POST['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="out_of_order" <?php echo ($_POST['status'] ?? '') === 'out_of_order' ? 'selected' : ''; ?>>Out of Order</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="purchase_date" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Purchase Date</label>
                            <div class="input-group">
                                <span class="input-group-text" style="color: #FFD700 !important; font-weight: 600 !important;">
                                    <i class="fas fa-calendar" style="color: #FFD700 !important;"></i>
                                </span>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" 
                                       value="<?php echo htmlspecialchars($_POST['purchase_date'] ?? date('Y-m-d')); ?>"
                                       style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="last_maintenance" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Last Maintenance</label>
                            <div class="input-group">
                                <span class="input-group-text" style="color: #FFD700 !important; font-weight: 600 !important;">
                                    <i class="fas fa-wrench" style="color: #FFD700 !important;"></i>
                                </span>
                                <input type="date" class="form-control" id="last_maintenance" name="last_maintenance" 
                                       value="<?php echo htmlspecialchars($_POST['last_maintenance'] ?? ''); ?>"
                                       style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="condition" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Condition</label>
                            <div class="input-group">
                                <span class="input-group-text" style="color: #FFD700 !important; font-weight: 600 !important;">
                                    <i class="fas fa-check-circle" style="color: #FFD700 !important;"></i>
                                </span>
                                <select class="form-select" id="condition" name="condition"
                                        style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;">
                                    <option value="Excellent">Excellent</option>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label" style="color: #FFD700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.9) !important; font-weight: 700 !important;">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Enter equipment description, specifications, or notes"
                                  style="color: #FFFFFF !important; background: rgba(0,0,0,0.8) !important; border: 2px solid #FFD700 !important; padding: 12px 20px !important;"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="equipment.php" class="btn btn-outline-secondary" style="color: #FFFFFF !important; border-color: #FFD700 !important; border-width: 2px !important; font-weight: 600 !important;">
                                    <i class="fas fa-arrow-left me-2" style="color: #FFD700 !important;"></i>Back to Equipment
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-outline-warning me-2" style="color: #FFFFFF !important; border-color: #FFD700 !important; border-width: 2px !important; font-weight: 600 !important;">
                                        <i class="fas fa-undo me-2" style="color: #FFD700 !important;"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%) !important; color: #FFFFFF !important; border: none; font-weight: 700 !important;">
                                        <i class="fas fa-save me-2" style="color: #FFFFFF !important;"></i>Add Equipment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-set today's date as default for purchase date
        document.addEventListener('DOMContentLoaded', function() {
            const purchaseDate = document.getElementById('purchase_date');
            if (purchaseDate && !purchaseDate.value) {
                purchaseDate.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>
