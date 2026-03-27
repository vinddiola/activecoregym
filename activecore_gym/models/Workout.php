<?php
/**
 * Workout Model
 * Manages workout plans and exercise relationships
 */

require_once __DIR__ . '/../config/database.php';

class Workout {
    private $conn;
    private $table_name = "workouts";
    private $exercises_table = "workout_exercises";

    public $workout_id;
    public $title;
    public $description;
    public $purpose;
    public $difficulty_level;
    public $duration_minutes;
    public $equipment_required;
    public $instructions;
    public $is_active;
    public $created_by;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new workout
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (title, description, purpose, difficulty_level, duration_minutes, equipment_required, instructions, is_active, created_by) 
                 VALUES (:title, :description, :purpose, :difficulty_level, :duration_minutes, :equipment_required, :instructions, :is_active, :created_by)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->purpose = htmlspecialchars(strip_tags($this->purpose));
        $this->difficulty_level = htmlspecialchars(strip_tags($this->difficulty_level));
        $this->duration_minutes = htmlspecialchars(strip_tags($this->duration_minutes));
        $this->equipment_required = htmlspecialchars(strip_tags($this->equipment_required));
        $this->instructions = htmlspecialchars(strip_tags($this->instructions));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));

        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":purpose", $this->purpose);
        $stmt->bindParam(":difficulty_level", $this->difficulty_level);
        $stmt->bindParam(":duration_minutes", $this->duration_minutes);
        $stmt->bindParam(":equipment_required", $this->equipment_required);
        $stmt->bindParam(":instructions", $this->instructions);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":created_by", $this->created_by);

        if ($stmt->execute()) {
            $this->workout_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read single workout
    public function readOne() {
        $query = "SELECT w.*, u.username as created_by_name 
                 FROM " . $this->table_name . " w
                 LEFT JOIN users u ON w.created_by = u.user_id
                 WHERE w.workout_id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->workout_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->purpose = $row['purpose'];
            $this->difficulty_level = $row['difficulty_level'];
            $this->duration_minutes = $row['duration_minutes'];
            $this->equipment_required = $row['equipment_required'];
            $this->instructions = $row['instructions'];
            $this->is_active = $row['is_active'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Read all workouts with pagination
    public function getAll($limit = 10, $offset = 0, $difficulty = null, $purpose = null) {
        $query = "SELECT w.*, u.username as created_by_name,
                 COUNT(DISTINCT we.exercise_id) as exercise_count
                 FROM " . $this->table_name . " w
                 LEFT JOIN users u ON w.created_by = u.user_id
                 LEFT JOIN " . $this->exercises_table . " we ON w.workout_id = we.workout_id
                 WHERE w.is_active = 1";
        
        $params = [];
        
        if ($difficulty) {
            $query .= " AND w.difficulty_level = ?";
            $params[] = $difficulty;
        }
        
        if ($purpose) {
            $query .= " AND w.purpose = ?";
            $params[] = $purpose;
        }
        
        $query .= " GROUP BY w.workout_id ORDER BY w.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($query);
        
        for ($i = 0; $i < count($params); $i++) {
            if ($i >= count($params) - 2) {
                // Last two parameters are LIMIT and OFFSET - bind as integers
                $stmt->bindValue($i + 1, $params[$i], PDO::PARAM_INT);
            } else {
                $stmt->bindValue($i + 1, $params[$i]);
            }
        }
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get workouts by difficulty level for members
    public function getByDifficulty($difficulty, $limit = 10) {
        $query = "SELECT w.*, u.username as created_by_name,
                 COUNT(DISTINCT we.exercise_id) as exercise_count
                 FROM " . $this->table_name . " w
                 LEFT JOIN users u ON w.created_by = u.user_id
                 LEFT JOIN " . $this->exercises_table . " we ON w.workout_id = we.workout_id
                 WHERE w.is_active = 1 AND w.difficulty_level = ?
                 GROUP BY w.workout_id 
                 ORDER BY w.created_at DESC 
                 LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $difficulty);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update workout
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET title = :title, description = :description, purpose = :purpose, 
                     difficulty_level = :difficulty_level, duration_minutes = :duration_minutes,
                     equipment_required = :equipment_required, instructions = :instructions,
                     is_active = :is_active, updated_at = CURRENT_TIMESTAMP
                 WHERE workout_id = :workout_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->purpose = htmlspecialchars(strip_tags($this->purpose));
        $this->difficulty_level = htmlspecialchars(strip_tags($this->difficulty_level));
        $this->duration_minutes = htmlspecialchars(strip_tags($this->duration_minutes));
        $this->equipment_required = htmlspecialchars(strip_tags($this->equipment_required));
        $this->instructions = htmlspecialchars(strip_tags($this->instructions));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $this->workout_id = htmlspecialchars(strip_tags($this->workout_id));

        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":purpose", $this->purpose);
        $stmt->bindParam(":difficulty_level", $this->difficulty_level);
        $stmt->bindParam(":duration_minutes", $this->duration_minutes);
        $stmt->bindParam(":equipment_required", $this->equipment_required);
        $stmt->bindParam(":instructions", $this->instructions);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":workout_id", $this->workout_id);

        return $stmt->execute();
    }

    // Delete workout (soft delete - set is_active to 0)
    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE workout_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->workout_id);
        return $stmt->execute();
    }

    // Get workout exercises
    public function getExercises() {
        $query = "SELECT we.*, e.name, e.instructions as exercise_instructions, e.difficulty_level as exercise_difficulty, e.muscle_group
                 FROM " . $this->exercises_table . " we
                 LEFT JOIN exercises e ON we.exercise_id = e.exercise_id
                 WHERE we.workout_id = ?
                 ORDER BY we.order_index";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->workout_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add exercise to workout
    public function addExercise($exercise_id, $sets, $reps = null, $weight = null, $duration_seconds = null, $rest_seconds = 60, $order_index = 1, $notes = null) {
        $query = "INSERT INTO " . $this->exercises_table . " 
                 (workout_id, exercise_id, sets, reps, weight, duration_seconds, rest_seconds, order_index, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->workout_id, $exercise_id, $sets, $reps, $weight, $duration_seconds, $rest_seconds, $order_index, $notes]);
    }

    // Remove exercise from workout
    public function removeExercise($exercise_id) {
        $query = "DELETE FROM " . $this->exercises_table . " WHERE workout_id = ? AND exercise_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->workout_id, $exercise_id]);
    }

    // Get workout statistics
    public function getStats() {
        $query = "SELECT 
                 COUNT(*) as total_workouts,
                 SUM(CASE WHEN difficulty_level = 'beginner' THEN 1 ELSE 0 END) as beginner_count,
                 SUM(CASE WHEN difficulty_level = 'intermediate' THEN 1 ELSE 0 END) as intermediate_count,
                 SUM(CASE WHEN difficulty_level = 'advanced' THEN 1 ELSE 0 END) as advanced_count,
                 SUM(CASE WHEN purpose = 'strength' THEN 1 ELSE 0 END) as strength_count,
                 SUM(CASE WHEN purpose = 'cardio' THEN 1 ELSE 0 END) as cardio_count,
                 SUM(CASE WHEN purpose = 'flexibility' THEN 1 ELSE 0 END) as flexibility_count
                 FROM " . $this->table_name . " 
                 WHERE is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Search workouts
    public function search($keyword, $limit = 10) {
        $query = "SELECT w.*, u.username as created_by_name,
                 COUNT(DISTINCT we.exercise_id) as exercise_count
                 FROM " . $this->table_name . " w
                 LEFT JOIN users u ON w.created_by = u.user_id
                 LEFT JOIN " . $this->exercises_table . " we ON w.workout_id = we.workout_id
                 WHERE w.is_active = 1 AND (w.title LIKE ? OR w.description LIKE ? OR w.purpose LIKE ?)
                 GROUP BY w.workout_id 
                 ORDER BY w.created_at DESC 
                 LIMIT ?";

        $search_term = "%" . $keyword . "%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $search_term);
        $stmt->bindParam(2, $search_term);
        $stmt->bindParam(3, $search_term);
        $stmt->bindParam(4, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get count of workouts
    public function getCount($difficulty = null, $purpose = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE is_active = 1";
        
        if ($difficulty && $purpose) {
            $query .= " AND difficulty_level = ? AND purpose = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $difficulty);
            $stmt->bindParam(2, $purpose);
        } elseif ($difficulty) {
            $query .= " AND difficulty_level = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $difficulty);
        } elseif ($purpose) {
            $query .= " AND purpose = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $purpose);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>
