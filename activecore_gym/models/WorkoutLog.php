<?php
/**
 * WorkoutLog Model
 * Manages member workout tracking and progress
 */

require_once __DIR__ . '/../config/database.php';

class WorkoutLog {
    private $conn;
    private $table_name = "workout_logs";

    public $log_id;
    public $member_id;
    public $exercise_id;
    public $workout_date;
    public $sets;
    public $reps;
    public $weight;
    public $duration_minutes;
    public $notes;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new workout log
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (member_id, exercise_id, workout_date, sets, reps, weight, duration_minutes, notes) 
                 VALUES (:member_id, :exercise_id, :workout_date, :sets, :reps, :weight, :duration_minutes, :notes)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->member_id = htmlspecialchars(strip_tags($this->member_id));
        $this->exercise_id = htmlspecialchars(strip_tags($this->exercise_id));
        $this->workout_date = htmlspecialchars(strip_tags($this->workout_date));
        $this->sets = htmlspecialchars(strip_tags($this->sets));
        $this->reps = htmlspecialchars(strip_tags($this->reps));
        $this->weight = htmlspecialchars(strip_tags($this->weight));
        $this->duration_minutes = htmlspecialchars(strip_tags($this->duration_minutes));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind values
        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":exercise_id", $this->exercise_id);
        $stmt->bindParam(":workout_date", $this->workout_date);
        $stmt->bindParam(":sets", $this->sets);
        $stmt->bindParam(":reps", $this->reps);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":duration_minutes", $this->duration_minutes);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            $this->log_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get workout log by ID
    public function getById($log_id) {
        $query = "SELECT wl.*, e.name as exercise_name, e.difficulty_level, e.muscle_group,
                        m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " wl
                 JOIN exercises e ON wl.exercise_id = e.exercise_id
                 JOIN members m ON wl.member_id = m.member_id
                 WHERE wl.log_id = :log_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":log_id", $log_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->log_id = $row['log_id'];
            $this->member_id = $row['member_id'];
            $this->exercise_id = $row['exercise_id'];
            $this->workout_date = $row['workout_date'];
            $this->sets = $row['sets'];
            $this->reps = $row['reps'];
            $this->weight = $row['weight'];
            $this->duration_minutes = $row['duration_minutes'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            return $row;
        }
        return false;
    }

    // Update workout log
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET sets = :sets, reps = :reps, weight = :weight, duration_minutes = :duration_minutes, notes = :notes
                 WHERE log_id = :log_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->sets = htmlspecialchars(strip_tags($this->sets));
        $this->reps = htmlspecialchars(strip_tags($this->reps));
        $this->weight = htmlspecialchars(strip_tags($this->weight));
        $this->duration_minutes = htmlspecialchars(strip_tags($this->duration_minutes));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        // Bind values
        $stmt->bindParam(":sets", $this->sets);
        $stmt->bindParam(":reps", $this->reps);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":duration_minutes", $this->duration_minutes);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":log_id", $this->log_id);

        return $stmt->execute();
    }

    // Delete workout log
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE log_id = :log_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":log_id", $this->log_id);

        return $stmt->execute();
    }

    // Get workout logs by member
    public function getByMember($member_id, $limit = 50, $offset = 0) {
        $query = "SELECT wl.*, e.name as exercise_name, e.difficulty_level, e.muscle_group
                 FROM " . $this->table_name . " wl
                 JOIN exercises e ON wl.exercise_id = e.exercise_id
                 WHERE wl.member_id = :member_id
                 ORDER BY wl.workout_date DESC, wl.created_at DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get member's recent workouts (alias for getByMember)
    public function getRecentWorkouts($member_id, $limit = 10) {
        return $this->getByMember($member_id, $limit);
    }

    // Get workout logs by exercise
    public function getByExercise($exercise_id, $limit = 50) {
        $query = "SELECT wl.*, m.first_name as member_first_name, m.last_name as member_last_name
                 FROM " . $this->table_name . " wl
                 JOIN members m ON wl.member_id = m.member_id
                 WHERE wl.exercise_id = :exercise_id
                 ORDER BY wl.workout_date DESC, wl.created_at DESC 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":exercise_id", $exercise_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get workout logs by date range
    public function getByDateRange($member_id, $start_date, $end_date) {
        $query = "SELECT wl.*, e.name as exercise_name, e.difficulty_level, e.muscle_group
                 FROM " . $this->table_name . " wl
                 JOIN exercises e ON wl.exercise_id = e.exercise_id
                 WHERE wl.member_id = :member_id 
                 AND wl.workout_date BETWEEN :start_date AND :end_date
                 ORDER BY wl.workout_date DESC, wl.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get member's workout statistics
    public function getMemberStats($member_id) {
        $query = "SELECT 
                    COUNT(*) as total_workouts,
                    COUNT(DISTINCT workout_date) as workout_days,
                    SUM(sets) as total_sets,
                    SUM(reps) as total_reps,
                    AVG(weight) as avg_weight,
                    MAX(weight) as max_weight,
                    SUM(duration_minutes) as total_duration,
                    MAX(workout_date) as last_workout,
                    MIN(workout_date) as first_workout
                 FROM " . $this->table_name . " 
                 WHERE member_id = :member_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get exercise progress over time
    public function getExerciseProgress($member_id, $exercise_id, $limit = 20) {
        $query = "SELECT workout_date, sets, reps, weight, created_at
                 FROM " . $this->table_name . " 
                 WHERE member_id = :member_id AND exercise_id = :exercise_id
                 ORDER BY workout_date ASC, created_at ASC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":exercise_id", $exercise_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get monthly workout statistics
    public function getMonthlyStats($member_id, $year, $month) {
        $query = "SELECT 
                    COUNT(*) as total_workouts,
                    COUNT(DISTINCT workout_date) as workout_days,
                    SUM(sets) as total_sets,
                    SUM(reps) as total_reps,
                    AVG(weight) as avg_weight,
                    MAX(weight) as max_weight,
                    SUM(duration_minutes) as total_duration
                 FROM " . $this->table_name . "
                 WHERE member_id = :member_id 
                 AND YEAR(workout_date) = :year AND MONTH(workout_date) = :month";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":year", $year, PDO::PARAM_INT);
        $stmt->bindParam(":month", $month, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get most performed exercises by member
    public function getMostPerformedExercises($member_id, $limit = 10) {
        $query = "SELECT e.name, e.exercise_id, COUNT(*) as frequency,
                        AVG(wl.weight) as avg_weight, MAX(wl.weight) as max_weight,
                        SUM(wl.sets) as total_sets, SUM(wl.reps) as total_reps
                 FROM " . $this->table_name . " wl
                 JOIN exercises e ON wl.exercise_id = e.exercise_id
                 WHERE wl.member_id = :member_id
                 GROUP BY wl.exercise_id, e.name, e.exercise_id
                 ORDER BY frequency DESC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get personal bests for member
    public function getPersonalBests($member_id, $limit = 10) {
        $query = "SELECT e.name, e.muscle_group, MAX(wl.weight) as max_weight,
                        MAX(wl.reps) as max_reps, MAX(wl.sets) as max_sets,
                        wl.workout_date as achieved_date
                 FROM " . $this->table_name . " wl
                 JOIN exercises e ON wl.exercise_id = e.exercise_id
                 WHERE wl.member_id = :member_id
                 GROUP BY wl.exercise_id, e.name, e.muscle_group
                 ORDER BY max_weight DESC, max_reps DESC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get workout frequency (last 30 days)
    public function getWorkoutFrequency($member_id, $days = 30) {
        $query = "SELECT DATE(workout_date) as workout_date, COUNT(*) as workouts
                 FROM " . $this->table_name . " 
                 WHERE member_id = :member_id 
                 AND workout_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                 GROUP BY DATE(workout_date)
                 ORDER BY workout_date ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":days", $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get muscle group distribution
    public function getMuscleGroupDistribution($member_id) {
        $query = "SELECT e.muscle_group, COUNT(*) as frequency,
                        SUM(wl.sets) as total_sets, SUM(wl.reps) as total_reps,
                        AVG(wl.weight) as avg_weight
                 FROM " . $this->table_name . " wl
                 JOIN exercises e ON wl.exercise_id = e.exercise_id
                 WHERE wl.member_id = :member_id
                 GROUP BY e.muscle_group
                 ORDER BY frequency DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search workout logs
    public function search($member_id, $keyword) {
        $query = "SELECT wl.*, e.name as exercise_name, e.difficulty_level, e.muscle_group
                 FROM " . $this->table_name . " wl
                 JOIN exercises e ON wl.exercise_id = e.exercise_id
                 WHERE wl.member_id = :member_id 
                 AND (e.name LIKE :keyword OR e.muscle_group LIKE :keyword OR wl.notes LIKE :keyword)
                 ORDER BY wl.workout_date DESC, wl.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get overall workout statistics (admin)
    public function getOverallStats() {
        $query = "SELECT 
                    COUNT(*) as total_logs,
                    COUNT(DISTINCT member_id) as active_members,
                    COUNT(DISTINCT exercise_id) as exercises_performed,
                    COUNT(DISTINCT workout_date) as workout_days,
                    SUM(sets) as total_sets,
                    SUM(reps) as total_reps,
                    AVG(weight) as avg_weight,
                    MAX(weight) as max_weight
                 FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
