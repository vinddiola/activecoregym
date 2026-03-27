<?php
/**
 * Equipment Model
 * Manages gym equipment inventory and status
 */

require_once __DIR__ . '/../config/database.php';

class Equipment {
    private $conn;
    private $table_name = "equipment";

    public $equipment_id;
    public $name;
    public $category;
    public $status;
    public $purchase_date;
    public $last_maintenance;
    public $description;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new equipment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (name, category, status, purchase_date, last_maintenance, description) 
                 VALUES (:name, :category, :status, :purchase_date, :last_maintenance, :description)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->last_maintenance = htmlspecialchars(strip_tags($this->last_maintenance));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":last_maintenance", $this->last_maintenance);
        $stmt->bindParam(":description", $this->description);

        if ($stmt->execute()) {
            $this->equipment_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get equipment by ID
    public function getById($equipment_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE equipment_id = :equipment_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":equipment_id", $equipment_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->equipment_id = $row['equipment_id'];
            $this->name = $row['name'];
            $this->category = $row['category'];
            $this->status = $row['status'];
            $this->purchase_date = $row['purchase_date'];
            $this->last_maintenance = $row['last_maintenance'];
            $this->description = $row['description'];
            return $row;
        }
        return false;
    }

    // Update equipment
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET name = :name, category = :category, status = :status, 
                     purchase_date = :purchase_date, last_maintenance = :last_maintenance, description = :description
                 WHERE equipment_id = :equipment_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->purchase_date = htmlspecialchars(strip_tags($this->purchase_date));
        $this->last_maintenance = htmlspecialchars(strip_tags($this->last_maintenance));
        $this->description = htmlspecialchars(strip_tags($this->description));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":last_maintenance", $this->last_maintenance);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":equipment_id", $this->equipment_id);

        return $stmt->execute();
    }

    // Update equipment status
    public function updateStatus($new_status) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = :status 
                 WHERE equipment_id = :equipment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $new_status);
        $stmt->bindParam(":equipment_id", $this->equipment_id);

        return $stmt->execute();
    }

    // Delete equipment
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE equipment_id = :equipment_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":equipment_id", $this->equipment_id);

        return $stmt->execute();
    }

    // Get all equipment
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 ORDER BY category ASC, name ASC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get equipment by status
    public function getByStatus($status) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE status = :status
                 ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get equipment by category
    public function getByCategory($category) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE category = :category
                 ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $category);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get available equipment
    public function getAvailable() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE status = 'available'
                 ORDER BY category ASC, name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get equipment needing maintenance
    public function getMaintenanceNeeded() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE status = 'maintenance' OR status = 'out_of_order'
                 ORDER BY last_maintenance ASC, name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search equipment
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE (name LIKE :keyword OR category LIKE :keyword OR description LIKE :keyword)
                 ORDER BY category ASC, name ASC";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get featured equipment for landing page
    public function getFeaturedEquipment($limit = 6) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE status = 'available'
                 ORDER BY RAND() 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get equipment statistics
    public function getStats() {
        $query = "SELECT status, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 GROUP BY status";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get equipment count by category
    public function getCountByCategory() {
        $query = "SELECT category, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 GROUP BY category";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get total equipment count
    public function getCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Get all categories
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table_name . " 
                 ORDER BY category ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Mark equipment as under maintenance
    public function setMaintenanceMode($equipment_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = 'maintenance', last_maintenance = CURDATE()
                 WHERE equipment_id = :equipment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":equipment_id", $equipment_id);

        return $stmt->execute();
    }

    // Mark equipment as available after maintenance
    public function setAvailable($equipment_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = 'available'
                 WHERE equipment_id = :equipment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":equipment_id", $equipment_id);

        return $stmt->execute();
    }

    // Mark equipment as in use
    public function setInUse($equipment_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = 'in_use'
                 WHERE equipment_id = :equipment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":equipment_id", $equipment_id);

        return $stmt->execute();
    }

    // Get equipment that needs maintenance soon (based on last maintenance date)
    public function getUpcomingMaintenance($days = 30) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE last_maintenance <= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                 AND status != 'maintenance'
                 ORDER BY last_maintenance ASC
                 LIMIT 10";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":days", $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get equipment usage statistics (how many exercises use each equipment)
    public function getUsageStats() {
        $query = "SELECT e.*, COUNT(ee.exercise_id) as usage_count
                 FROM " . $this->table_name . " e
                 LEFT JOIN exercise_equipment ee ON e.equipment_id = ee.equipment_id
                 GROUP BY e.equipment_id
                 ORDER BY usage_count DESC, e.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
