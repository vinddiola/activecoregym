<?php
/**
 * Coach Model
 * Manages coach information and sessions
 */

require_once __DIR__ . '/../config/database.php';

class Coach {
    private $conn;
    private $table_name = "coaches";

    public $coach_id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $specialization;
    public $experience_years;
    public $rating;
    public $phone;
    public $is_available;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new coach
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (user_id, first_name, last_name, specialization, experience_years, phone, is_available) 
                 VALUES (:user_id, :first_name, :last_name, :specialization, :experience_years, :phone, :is_available)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->specialization = htmlspecialchars(strip_tags($this->specialization));
        $this->experience_years = htmlspecialchars(strip_tags($this->experience_years));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->is_available = htmlspecialchars(strip_tags($this->is_available));

        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":specialization", $this->specialization);
        $stmt->bindParam(":experience_years", $this->experience_years);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":is_available", $this->is_available);

        if ($stmt->execute()) {
            $this->coach_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get coach by user ID
    public function getByUserId($user_id) {
        $query = "SELECT c.*, u.username, u.email, u.created_at as user_created_at 
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.user_id
                 WHERE c.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->coach_id = $row['coach_id'];
            $this->user_id = $row['user_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->specialization = $row['specialization'];
            $this->experience_years = $row['experience_years'];
            $this->rating = $row['rating'];
            $this->phone = $row['phone'];
            $this->is_available = $row['is_available'];
            return $row;
        }
        return false;
    }

    // Get coach by coach ID
    public function getById($coach_id) {
        $query = "SELECT c.*, u.username, u.email, u.created_at as user_created_at 
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.user_id
                 WHERE c.coach_id = :coach_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coach_id", $coach_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update coach information
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET first_name = :first_name, last_name = :last_name, specialization = :specialization, 
                     experience_years = :experience_years, phone = :phone, is_available = :is_available
                 WHERE coach_id = :coach_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->specialization = htmlspecialchars(strip_tags($this->specialization));
        $this->experience_years = htmlspecialchars(strip_tags($this->experience_years));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->is_available = htmlspecialchars(strip_tags($this->is_available));

        // Bind values
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":specialization", $this->specialization);
        $stmt->bindParam(":experience_years", $this->experience_years);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":is_available", $this->is_available);
        $stmt->bindParam(":coach_id", $this->coach_id);

        return $stmt->execute();
    }

    // Get all available coaches
    public function getAvailableCoaches() {
        $query = "SELECT c.*, u.username, u.email 
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.user_id
                 WHERE c.is_available = TRUE AND u.is_active = TRUE
                 ORDER BY c.rating DESC, c.first_name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all coaches
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT c.*, u.username, u.email, u.is_active 
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.user_id
                 ORDER BY c.first_name ASC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update coach rating
    public function updateRating() {
        $query = "UPDATE " . $this->table_name . " 
                 SET rating = (
                     SELECT COALESCE(AVG(rating), 0) 
                     FROM coach_ratings 
                     WHERE coach_id = :coach_id
                 )
                 WHERE coach_id = :coach_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coach_id", $this->coach_id);
        return $stmt->execute();
    }

    // Get coach's upcoming sessions
    public function getUpcomingSessions($coach_id) {
        $query = "SELECT cs.*, m.first_name as member_first_name, m.last_name as member_last_name
                 FROM coach_sessions cs
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.coach_id = :coach_id 
                 AND cs.session_date >= CURDATE()
                 AND cs.status IN ('pending', 'confirmed')
                 ORDER BY cs.session_date ASC, cs.session_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coach_id", $coach_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get coach's session history
    public function getSessionHistory($coach_id, $limit = 20) {
        $query = "SELECT cs.*, m.first_name as member_first_name, m.last_name as member_last_name
                 FROM coach_sessions cs
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.coach_id = :coach_id 
                 ORDER BY cs.session_date DESC, cs.session_time DESC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coach_id", $coach_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get coach's daily schedule
    public function getDailySchedule($coach_id, $date) {
        $query = "SELECT cs.*, m.first_name as member_first_name, m.last_name as member_last_name
                 FROM coach_sessions cs
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.coach_id = :coach_id AND cs.session_date = :date
                 ORDER BY cs.session_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coach_id", $coach_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get coach's weekly schedule
    public function getWeeklySchedule($coach_id, $start_date, $end_date) {
        $query = "SELECT cs.*, m.first_name as member_first_name, m.last_name as member_last_name
                 FROM coach_sessions cs
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.coach_id = :coach_id 
                 AND cs.session_date BETWEEN :start_date AND :end_date
                 ORDER BY cs.session_date ASC, cs.session_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coach_id", $coach_id);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get coach statistics
    public function getStats($coach_id) {
        $query = "SELECT COUNT(*) as total_sessions,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_sessions,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_sessions,
                        COUNT(DISTINCT member_id) as unique_members
                 FROM coach_sessions 
                 WHERE coach_id = :coach_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coach_id", $coach_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Search coaches
    public function search($keyword) {
        $query = "SELECT c.*, u.username, u.email 
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.user_id
                 WHERE (c.first_name LIKE :keyword OR c.last_name LIKE :keyword OR c.specialization LIKE :keyword OR u.username LIKE :keyword)
                 AND u.is_active = TRUE
                 ORDER BY c.rating DESC, c.first_name ASC";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get top rated coaches
    public function getTopRated($limit = 5) {
        $query = "SELECT c.*, u.username, u.email 
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.user_id
                 WHERE c.is_available = TRUE AND u.is_active = TRUE
                 ORDER BY c.rating DESC, c.first_name ASC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get coach count by user type
    public function getCountByType($user_type = 'coach') {
        $query = "SELECT COUNT(*) as count 
                 FROM " . $this->table_name . " c
                 JOIN users u ON c.user_id = u.user_id
                 WHERE u.user_type = :user_type AND u.is_active = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_type", $user_type);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>
