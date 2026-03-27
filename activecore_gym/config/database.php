<?php
/**
 * Database Configuration
 * ActiveCore Gym Management System
 */

class Database {
    private $host = "localhost";
    private $db_name = "activecore_gym";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Helper function for database connection
function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

// Session configuration
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base URL
define('BASE_URL', 'http://localhost/activecore_gym/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Security settings
define('HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_LIFETIME', 3600); // 1 hour

// Auto logout after inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}
$_SESSION['last_activity'] = time();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user type
function getUserType() {
    return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
}

// Redirect based on user type
function redirectBasedOnUserType() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "views/auth/login.php");
        exit();
    }

    $userType = getUserType();
    switch ($userType) {
        case 'admin':
            header("Location: " . BASE_URL . "views/admin/dashboard.php");
            break;
        case 'coach':
            header("Location: " . BASE_URL . "views/coach/dashboard.php");
            break;
        case 'member':
            header("Location: " . BASE_URL . "views/member/dashboard.php");
            break;
        default:
            session_destroy();
            header("Location: " . BASE_URL . "views/auth/login.php");
            break;
    }
    exit();
}

// Check if user has access to specific area
function checkAccess($requiredType) {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "views/auth/login.php");
        exit();
    }

    $userType = getUserType();
    if ($userType !== $requiredType && $userType !== 'admin') {
        $_SESSION['error'] = "Access denied. You don't have permission to access this area.";
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Display success/error messages
function displayMessages() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Format time
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
