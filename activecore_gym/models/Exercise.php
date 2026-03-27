<?php
/**
 * Exercise Model
 * Manages exercise database and recommendations
 */

require_once __DIR__ . '/../config/database.php';

class Exercise {
    private $conn;
    private $table_name = "exercises";

    public $exercise_id;
    public $name;
    public $difficulty_level;
    public $instructions;
    public $equipment_needed;
    public $muscle_group;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new exercise
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (name, difficulty_level, instructions, equipment_needed, muscle_group) 
                 VALUES (:name, :difficulty_level, :instructions, :equipment_needed, :muscle_group)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->difficulty_level = htmlspecialchars(strip_tags($this->difficulty_level));
        $this->instructions = htmlspecialchars(strip_tags($this->instructions));
        $this->equipment_needed = htmlspecialchars(strip_tags($this->equipment_needed));
        $this->muscle_group = htmlspecialchars(strip_tags($this->muscle_group));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":difficulty_level", $this->difficulty_level);
        $stmt->bindParam(":instructions", $this->instructions);
        $stmt->bindParam(":equipment_needed", $this->equipment_needed);
        $stmt->bindParam(":muscle_group", $this->muscle_group);

        if ($stmt->execute()) {
            $this->exercise_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get exercise by ID
    public function getById($exercise_id) {
        $query = "SELECT e.*, GROUP_CONCAT(eq.name SEPARATOR ', ') as equipment_list
                 FROM " . $this->table_name . " e
                 LEFT JOIN exercise_equipment ee ON e.exercise_id = ee.exercise_id
                 LEFT JOIN equipment eq ON ee.equipment_id = eq.equipment_id
                 WHERE e.exercise_id = :exercise_id
                 GROUP BY e.exercise_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":exercise_id", $exercise_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update exercise
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET name = :name, difficulty_level = :difficulty_level, instructions = :instructions, 
                     equipment_needed = :equipment_needed, muscle_group = :muscle_group
                 WHERE exercise_id = :exercise_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->difficulty_level = htmlspecialchars(strip_tags($this->difficulty_level));
        $this->instructions = htmlspecialchars(strip_tags($this->instructions));
        $this->equipment_needed = htmlspecialchars(strip_tags($this->equipment_needed));
        $this->muscle_group = htmlspecialchars(strip_tags($this->muscle_group));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":difficulty_level", $this->difficulty_level);
        $stmt->bindParam(":instructions", $this->instructions);
        $stmt->bindParam(":equipment_needed", $this->equipment_needed);
        $stmt->bindParam(":muscle_group", $this->muscle_group);
        $stmt->bindParam(":exercise_id", $this->exercise_id);

        return $stmt->execute();
    }

    // Delete exercise
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE exercise_id = :exercise_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":exercise_id", $this->exercise_id);

        return $stmt->execute();
    }

    // Get all exercises
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT e.*, GROUP_CONCAT(eq.name SEPARATOR ', ') as equipment_list
                 FROM " . $this->table_name . " e
                 LEFT JOIN exercise_equipment ee ON e.exercise_id = ee.exercise_id
                 LEFT JOIN equipment eq ON ee.equipment_id = eq.equipment_id
                 GROUP BY e.exercise_id
                 ORDER BY e.name ASC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get exercises by difficulty level
    public function getByDifficulty($difficulty_level) {
        $query = "SELECT e.*, GROUP_CONCAT(eq.name SEPARATOR ', ') as equipment_list
                 FROM " . $this->table_name . " e
                 LEFT JOIN exercise_equipment ee ON e.exercise_id = ee.exercise_id
                 LEFT JOIN equipment eq ON ee.equipment_id = eq.equipment_id
                 WHERE e.difficulty_level = :difficulty_level
                 GROUP BY e.exercise_id
                 ORDER BY e.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":difficulty_level", $difficulty_level);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get exercises by muscle group
    public function getByMuscleGroup($muscle_group) {
        $query = "SELECT e.*, GROUP_CONCAT(eq.name SEPARATOR ', ') as equipment_list
                 FROM " . $this->table_name . " e
                 LEFT JOIN exercise_equipment ee ON e.exercise_id = ee.exercise_id
                 LEFT JOIN equipment eq ON ee.equipment_id = eq.equipment_id
                 WHERE e.muscle_group = :muscle_group
                 GROUP BY e.exercise_id
                 ORDER BY e.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":muscle_group", $muscle_group);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search exercises
    public function search($keyword) {
        $query = "SELECT e.*, GROUP_CONCAT(eq.name SEPARATOR ', ') as equipment_list
                 FROM " . $this->table_name . " e
                 LEFT JOIN exercise_equipment ee ON e.exercise_id = ee.exercise_id
                 LEFT JOIN equipment eq ON ee.equipment_id = eq.equipment_id
                 WHERE (e.name LIKE :keyword OR e.muscle_group LIKE :keyword OR e.instructions LIKE :keyword)
                 GROUP BY e.exercise_id
                 ORDER BY e.name ASC";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get beginner exercises (for recommendations)
    public function getBeginnerExercises($limit = 10) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE difficulty_level = 'beginner'
                 ORDER BY name ASC 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get intermediate exercises
    public function getIntermediateExercises($limit = 10) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE difficulty_level = 'intermediate'
                 ORDER BY name ASC 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get advanced exercises
    public function getAdvancedExercises($limit = 10) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE difficulty_level = 'advanced'
                 ORDER BY name ASC 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get featured exercises for landing page
    public function getFeaturedExercises($limit = 6) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 ORDER BY RAND() 
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get exercises that don't require equipment
    public function getBodyweightExercises() {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE equipment_needed = 'None' OR equipment_needed IS NULL
                 ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get exercise recommendations based on member's workout history
    public function getRecommendations($member_id, $limit = 5) {
        $query = "SELECT e.*, COUNT(wl.exercise_id) as frequency
                 FROM " . $this->table_name . " e
                 LEFT JOIN workout_logs wl ON e.exercise_id = wl.exercise_id AND wl.member_id = :member_id
                 WHERE e.exercise_id NOT IN (
                     SELECT DISTINCT exercise_id FROM workout_logs WHERE member_id = :member_id
                 )
                 GROUP BY e.exercise_id
                 ORDER BY e.difficulty_level ASC, frequency ASC
                 LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":member_id", $member_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get exercise count by difficulty
    public function getCountByDifficulty() {
        $query = "SELECT difficulty_level, COUNT(*) as count 
                 FROM " . $this->table_name . " 
                 GROUP BY difficulty_level";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all muscle groups
    public function getMuscleGroups() {
        $query = "SELECT DISTINCT muscle_group FROM " . $this->table_name . " 
                 WHERE muscle_group IS NOT NULL AND muscle_group != ''
                 ORDER BY muscle_group ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Link exercise to equipment
    public function linkToEquipment($exercise_id, $equipment_id) {
        $query = "INSERT INTO exercise_equipment (exercise_id, equipment_id) 
                 VALUES (:exercise_id, :equipment_id)
                 ON DUPLICATE KEY UPDATE exercise_id = exercise_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":exercise_id", $exercise_id);
        $stmt->bindParam(":equipment_id", $equipment_id);

        return $stmt->execute();
    }

    // Remove equipment link
    public function removeEquipmentLink($exercise_id, $equipment_id) {
        $query = "DELETE FROM exercise_equipment 
                 WHERE exercise_id = :exercise_id AND equipment_id = :equipment_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":exercise_id", $exercise_id);
        $stmt->bindParam(":equipment_id", $equipment_id);

        return $stmt->execute();
    }
}
?>
