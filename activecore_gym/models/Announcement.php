<?php
/**
 * Announcement Model
 * Manages gym announcements and notifications
 */

require_once __DIR__ . '/../config/database.php';

class Announcement {
    private $conn;
    private $table_name = "announcements";

    public $announcement_id;
    public $title;
    public $content;
    public $announcement_type;
    public $priority;
    public $created_at;
    public $expires_at;
    public $is_active;
    public $created_by;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new announcement
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (title, content, announcement_type, priority, expires_at, is_active, created_by) 
                 VALUES (:title, :content, :announcement_type, :priority, :expires_at, :is_active, :created_by)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->announcement_type = htmlspecialchars(strip_tags($this->announcement_type));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->expires_at = htmlspecialchars(strip_tags($this->expires_at));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));

        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":announcement_type", $this->announcement_type);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":expires_at", $this->expires_at);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->announcement_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get announcement by ID
    public function getById($announcement_id) {
        $query = "SELECT a.*, u.username as created_by_username 
                 FROM " . $this->table_name . " a
                 JOIN users u ON a.created_by = u.user_id
                 WHERE a.announcement_id = :announcement_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":announcement_id", $announcement_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->announcement_id = $row['announcement_id'];
            $this->title = $row['title'];
            $this->content = $row['content'];
            $this->announcement_type = $row['announcement_type'];
            $this->priority = $row['priority'];
            $this->created_at = $row['created_at'];
            $this->expires_at = $row['expires_at'];
            $this->is_active = $row['is_active'];
            $this->created_by = $row['created_by'];
            return $row;
        }
        return false;
    }

    // Update announcement
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET title = :title, content = :content, announcement_type = :announcement_type, 
                     priority = :priority, expires_at = :expires_at, is_active = :is_active
                 WHERE announcement_id = :announcement_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->announcement_type = htmlspecialchars(strip_tags($this->announcement_type));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->expires_at = htmlspecialchars(strip_tags($this->expires_at));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));

        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":announcement_type", $this->announcement_type);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":expires_at", $this->expires_at);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":announcement_id", $this->announcement_id);

        return $stmt->execute();
    }

    // Delete announcement
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE announcement_id = :announcement_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":announcement_id", $this->announcement_id);

        return $stmt->execute();
    }

    // Get all active announcements
    public function getActive($limit = 10) {
        $query = "SELECT a.*, u.username as created_by_username 
                 FROM " . $this->table_name . " a
                 JOIN users u ON a.created_by = u.user_id
                 WHERE a.is_active = TRUE 
                 AND (a.expires_at IS NULL OR a.expires_at >= CURDATE())
                 ORDER BY a.priority DESC, a.created_at DESC 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all announcements (admin)
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT a.*, u.username as created_by_username 
                 FROM " . $this->table_name . " a
                 JOIN users u ON a.created_by = u.user_id
                 ORDER BY a.created_at DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get announcements by type
    public function getByType($announcement_type) {
        $query = "SELECT a.*, u.username as created_by_username 
                 FROM " . $this->table_name . " a
                 JOIN users u ON a.created_by = u.user_id
                 WHERE a.announcement_type = :announcement_type AND a.is_active = TRUE
                 AND (a.expires_at IS NULL OR a.expires_at >= CURDATE())
                 ORDER BY a.priority DESC, a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":announcement_type", $announcement_type);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get high priority announcements
    public function getHighPriority() {
        $query = "SELECT a.*, u.username as created_by_username 
                 FROM " . $this->table_name . " a
                 JOIN users u ON a.created_by = u.user_id
                 WHERE a.priority = 'high' AND a.is_active = TRUE
                 AND (a.expires_at IS NULL OR a.expires_at >= CURDATE())
                 ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get latest announcements for landing page
    public function getLatest($limit = 3) {
        $query = "SELECT a.*, u.username as created_by_username 
                 FROM " . $this->table_name . " a
                 JOIN users u ON a.created_by = u.user_id
                 WHERE a.is_active = TRUE 
                 AND (a.expires_at IS NULL OR a.expires_at >= CURDATE())
                 ORDER BY a.created_at DESC 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search announcements
    public function search($keyword) {
        $query = "SELECT a.*, u.username as created_by_username 
                 FROM " . $this->table_name . " a
                 JOIN users u ON a.created_by = u.user_id
                 WHERE (a.title LIKE :keyword OR a.content LIKE :keyword)
                 ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Deactivate expired announcements
    public function deactivateExpired() {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_active = FALSE 
                 WHERE expires_at < CURDATE() AND is_active = TRUE";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    // Get announcement count by type
    public function getCountByType() {
        $query = "SELECT announcement_type, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 WHERE is_active = TRUE
                 AND (expires_at IS NULL OR expires_at >= CURDATE())
                 GROUP BY announcement_type";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get announcement statistics
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = TRUE THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = FALSE THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_count,
                    SUM(CASE WHEN expires_at < CURDATE() THEN 1 ELSE 0 END) as expired
                 FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Archive announcement (set as inactive)
    public function archive() {
        $this->is_active = false;
        return $this->update();
    }

    // Restore announcement (set as active)
    public function restore() {
        $this->is_active = true;
        return $this->update();
    }
}
?>
