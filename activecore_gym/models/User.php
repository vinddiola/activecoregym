<?php
/**
 * User Model
 * Base authentication model for all user types
 */

require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $email;
    public $password_hash;
    public $user_type;
    public $created_at;
    public $updated_at;
    public $is_active;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (username, email, password_hash, user_type) 
                 VALUES (:username, :email, :password_hash, :user_type)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash));
        $this->user_type = htmlspecialchars(strip_tags($this->user_type));

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":user_type", $this->user_type);

        if ($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Get user by ID
    public function getById($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->user_type = $row['user_type'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    // Get user by username or email
    public function getByUsernameOrEmail($login) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE username = :login OR email = :login";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":login", $login);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->user_type = $row['user_type'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    // Verify password
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    // Check if username exists
    public function usernameExists($username) {
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE username = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Update user
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET username = :username, email = :email, updated_at = CURRENT_TIMESTAMP
                 WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }

    // Update password
    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " 
                 SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP
                 WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $this->password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }

    // Deactivate user
    public function deactivate() {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP
                 WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }

    // Activate user
    public function activate() {
        $query = "UPDATE " . $this->table_name . " 
                 SET is_active = TRUE, updated_at = CURRENT_TIMESTAMP
                 WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }

    // Get all users (admin function)
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get users by type
    public function getByType($user_type) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE user_type = :user_type AND is_active = TRUE
                 ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_type", $user_type);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get user count by type
    public function getCountByType($user_type) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE user_type = :user_type AND is_active = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_type", $user_type);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>
