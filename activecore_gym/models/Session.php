<?php
/**
 * Session Model
 * Manages coach training sessions
 */

require_once __DIR__ . '/../config/database.php';

class Session {
    private $conn;
    private $table_name = "coach_sessions";

    public $session_id;
    public $coach_id;
    public $member_id;
    public $session_date;
    public $session_time;
    public $status;
    public $notes;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new session
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (coach_id, member_id, session_date, session_time, status, notes) 
                 VALUES (:coach_id, :member_id, :session_date, :session_time, :status, :notes)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->coach_id = htmlspecialchars(strip_tags($this->coach_id));
        $this->member_id = htmlspecialchars(strip_tags($this->member_id));
        $this->session_date = htmlspecialchars(strip_tags($this->session_date));
        $this->session_time = htmlspecialchars(strip_tags($this->session_time));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind values
        $stmt->bindParam(":coach_id", $this->coach_id);
        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":session_date", $this->session_date);
        $stmt->bindParam(":session_time", $this->session_time);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            $this->session_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get session by ID
    public function getById($session_id) {
        $query = "SELECT cs.*, 
                        c.first_name as coach_first_name, c.last_name as coach_last_name,
                        m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.session_id = :session_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->session_id = $row['session_id'];
            $this->coach_id = $row['coach_id'];
            $this->member_id = $row['member_id'];
            $this->session_date = $row['session_date'];
            $this->session_time = $row['session_time'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            return $row;
        }
        return false;
    }

    // Update session
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET coach_id = :coach_id, member_id = :member_id, session_date = :session_date, 
                     session_time = :session_time, status = :status, notes = :notes
                 WHERE session_id = :session_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->coach_id = htmlspecialchars(strip_tags($this->coach_id));
        $this->member_id = htmlspecialchars(strip_tags($this->member_id));
        $this->session_date = htmlspecialchars(strip_tags($this->session_date));
        $this->session_time = htmlspecialchars(strip_tags($this->session_time));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind values
        $stmt->bindParam(":coach_id", $this->coach_id);
        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":session_date", $this->session_date);
        $stmt->bindParam(":session_time", $this->session_time);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":session_id", $this->session_id);

        return $stmt->execute();
    }

    // Update session status
    public function updateStatus($new_status) {
        $query = "UPDATE " . $this->table_name . " 
                 SET status = :status 
                 WHERE session_id = :session_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $new_status);
        $stmt->bindParam(":session_id", $this->session_id);

        return $stmt->execute();
    }

    // Delete session
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE session_id = :session_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $this->session_id);

        return $stmt->execute();
    }

    // Get all sessions
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT cs.*, 
                        c.first_name as coach_first_name, c.last_name as coach_last_name,
                        m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 JOIN members m ON cs.member_id = m.member_id
                 ORDER BY cs.session_date DESC, cs.session_time DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get sessions by coach
    public function getByCoach($coach_id, $limit = 20) {
        $query = "SELECT cs.*, 
                        m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " cs
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

    // Get sessions by member
    public function getByMember($member_id, $limit = 20) {
        $query = "SELECT cs.*, 
                        c.first_name as coach_first_name, c.last_name as coach_last_name
                 FROM " . $this->table_name . " cs
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

    // Get sessions by status
    public function getByStatus($status) {
        $query = "SELECT cs.*, 
                        c.first_name as coach_first_name, c.last_name as coach_last_name,
                        m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.status = :status
                 ORDER BY cs.session_date ASC, cs.session_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get upcoming sessions
    public function getUpcoming($limit = 10) {
        $query = "SELECT cs.*, 
                        c.first_name as coach_first_name, c.last_name as coach_last_name,
                        m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.session_date >= CURDATE()
                 AND cs.status IN ('pending', 'confirmed')
                 ORDER BY cs.session_date ASC, cs.session_time ASC 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get member's upcoming sessions (alias for getUpcoming with member filter)
    public function getUpcomingSessions($member_id, $limit = 10) {
        $query = "SELECT cs.*, 
                        c.first_name as coach_first_name, c.last_name as coach_last_name
                 FROM " . $this->table_name . " cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 WHERE cs.member_id = :member_id 
                 AND cs.session_date >= CURDATE()
                 AND cs.status IN ('pending', 'confirmed')
                 ORDER BY cs.session_date ASC, cs.session_time ASC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get today's sessions
    public function getToday() {
        $query = "SELECT cs.*, 
                        c.first_name as coach_first_name, c.last_name as coach_last_name,
                        m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.session_date = CURDATE()
                 ORDER BY cs.session_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if coach is available at specific date and time
    public function isCoachAvailable($coach_id, $date, $time) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE coach_id = :coach_id AND session_date = :date AND session_time = :time
                 AND status IN ('pending', 'confirmed')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":coach_id", $coach_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":time", $time);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] == 0;
    }

    // Get session statistics
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_sessions,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_sessions,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_sessions,
                    COUNT(DISTINCT coach_id) as active_coaches,
                    COUNT(DISTINCT member_id) as active_members
                 FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get monthly session statistics
    public function getMonthlyStats($year, $month) {
        $query = "SELECT 
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                 FROM " . $this->table_name . "
                 WHERE YEAR(session_date) = :year AND MONTH(session_date) = :month";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":year", $year, PDO::PARAM_INT);
        $stmt->bindParam(":month", $month, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get sessions for a specific date range
    public function getByDateRange($start_date, $end_date) {
        $query = "SELECT cs.*, 
                        c.first_name as coach_first_name, c.last_name as coach_last_name,
                        m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " cs
                 JOIN coaches c ON cs.coach_id = c.coach_id
                 JOIN members m ON cs.member_id = m.member_id
                 WHERE cs.session_date BETWEEN :start_date AND :end_date
                 ORDER BY cs.session_date ASC, cs.session_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
