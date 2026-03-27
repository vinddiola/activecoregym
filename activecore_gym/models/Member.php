<?php
/**
 * Member Model
 * Extends User functionality for gym members
 */

require_once __DIR__ . '/../config/database.php';

class Member {
    private $conn;
    private $table_name = "members";

    public $member_id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $phone;
    public $membership_date;
    public $membership_status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new member
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (user_id, first_name, last_name, phone, membership_date, membership_status) 
                 VALUES (:user_id, :first_name, :last_name, :phone, :membership_date, :membership_status)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->membership_date = htmlspecialchars(strip_tags($this->membership_date));
        $this->membership_status = htmlspecialchars(strip_tags($this->membership_status));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":membership_date", $this->membership_date);
        $stmt->bindParam(":membership_status", $this->membership_status);

        if ($stmt->execute()) {
            $this->member_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get member by user ID
    public function getByUserId($user_id) {
        $query = "SELECT m.*, u.username, u.email, u.created_at as user_created_at 
                 FROM " . $this->table_name . " m
                 JOIN users u ON m.user_id = u.user_id
                 WHERE m.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->member_id = $row['member_id'];
            $this->user_id = $row['user_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->phone = $row['phone'];
            $this->membership_date = $row['membership_date'];
            $this->membership_status = $row['membership_status'];
            return $row;
        }
        return false;
    }

    // Get member by member ID
    public function getById($member_id) {
        $query = "SELECT m.*, u.username, u.email, u.created_at as user_created_at 
                 FROM " . $this->table_name . " m
                 JOIN users u ON m.user_id = u.user_id
                 WHERE m.member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update member information
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET first_name = :first_name, last_name = :last_name, phone = :phone, 
                     membership_status = :membership_status
                 WHERE member_id = :member_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->membership_status = htmlspecialchars(strip_tags($this->membership_status));

        // Bind values
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":membership_status", $this->membership_status);
        $stmt->bindParam(":member_id", $this->member_id);

        return $stmt->execute();
    }

    // Get all members
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT m.*, u.username, u.email, u.is_active 
                 FROM " . $this->table_name . " m
                 JOIN users u ON m.user_id = u.user_id
                 ORDER BY m.membership_date DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get active members count
    public function getActiveCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " m
                 JOIN users u ON m.user_id = u.user_id
                 WHERE m.membership_status = 'active' AND u.is_active = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }

    // Get members by membership status
    public function getByStatus($status) {
        $query = "SELECT m.*, u.username, u.email 
                 FROM " . $this->table_name . " m
                 JOIN users u ON m.user_id = u.user_id
                 WHERE m.membership_status = :status AND u.is_active = TRUE
                 ORDER BY m.membership_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search members
    public function search($keyword) {
        $query = "SELECT m.*, u.username, u.email 
                 FROM " . $this->table_name . " m
                 JOIN users u ON m.user_id = u.user_id
                 WHERE (m.first_name LIKE :keyword OR m.last_name LIKE :keyword OR u.username LIKE :keyword OR u.email LIKE :keyword)
                 AND u.is_active = TRUE
                 ORDER BY m.first_name ASC";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get member workout statistics
    public function getWorkoutStats($member_id) {
        $query = "SELECT COUNT(*) as total_workouts,
                        COUNT(DISTINCT workout_date) as workout_days,
                        SUM(sets) as total_sets,
                        SUM(reps) as total_reps,
                        AVG(weight) as avg_weight,
                        MAX(workout_date) as last_workout
                 FROM workout_logs 
                 WHERE member_id = :member_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get member's recent workouts
    public function getRecentWorkouts($member_id, $limit = 10) {
        $query = "SELECT wl.*, e.name as exercise_name, e.difficulty_level
                 FROM workout_logs wl
                 JOIN exercises e ON wl.exercise_id = e.exercise_id
                 WHERE wl.member_id = :member_id
                 ORDER BY wl.workout_date DESC, wl.created_at DESC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get member's upcoming coach sessions
    public function getUpcomingSessions($member_id) {
        $query = "SELECT cs.*, c.first_name as coach_first_name, c.last_name as coach_last_name
                 FROM coach_sessions cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 WHERE cs.member_id = :member_id 
                 AND cs.session_date >= CURDATE()
                 AND cs.status IN ('pending', 'confirmed')
                 ORDER BY cs.session_date ASC, cs.session_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get member's session history
    public function getSessionHistory($member_id, $limit = 20) {
        $query = "SELECT cs.*, c.first_name as coach_first_name, c.last_name as coach_last_name
                 FROM coach_sessions cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 WHERE cs.member_id = :member_id 
                 ORDER BY cs.session_date DESC, cs.session_time DESC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
