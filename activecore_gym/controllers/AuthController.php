<?php
/**
 * Authentication Controller
 * Handles user registration, login, and logout
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Coach.php';

class AuthController {
    private $user;
    private $member;
    private $coach;

    public function __construct() {
        $this->user = new User();
        $this->member = new Member();
        $this->coach = new Coach();
    }

    // Register new user
    public function register($userData, $profileData) {
        // Validate input
        if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if (strlen($userData['password']) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        }

        // Check if username or email already exists
        if ($this->user->usernameExists($userData['username'])) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        if ($this->user->emailExists($userData['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Create user
        $this->user->username = $userData['username'];
        $this->user->email = $userData['email'];
        $this->user->password_hash = password_hash($userData['password'], PASSWORD_DEFAULT);
        $this->user->user_type = $userData['user_type'];

        if (!$this->user->create()) {
            return ['success' => false, 'message' => 'Failed to create user account'];
        }

        // Create profile based on user type
        if ($userData['user_type'] === 'member') {
            $this->member->user_id = $this->user->user_id;
            $this->member->first_name = $profileData['first_name'];
            $this->member->last_name = $profileData['last_name'];
            $this->member->phone = isset($profileData['phone']) ? $profileData['phone'] : '';
            $this->member->membership_date = date('Y-m-d');
            $this->member->membership_status = 'active';

            if (!$this->member->create()) {
                // Rollback user creation if member profile fails
                $this->user->deactivate();
                return ['success' => false, 'message' => 'Failed to create member profile'];
            }
        } elseif ($userData['user_type'] === 'coach') {
            $this->coach->user_id = $this->user->user_id;
            $this->coach->first_name = $profileData['first_name'];
            $this->coach->last_name = $profileData['last_name'];
            $this->coach->specialization = isset($profileData['specialization']) ? $profileData['specialization'] : '';
            $this->coach->experience_years = isset($profileData['experience_years']) ? $profileData['experience_years'] : 0;
            $this->coach->phone = isset($profileData['phone']) ? $profileData['phone'] : '';
            $this->coach->is_available = true;

            if (!$this->coach->create()) {
                // Rollback user creation if coach profile fails
                $this->user->deactivate();
                return ['success' => false, 'message' => 'Failed to create coach profile'];
            }
        }

        return ['success' => true, 'message' => 'Registration successful! You can now login.'];
    }

    // Login user
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        // Get user by username or email
        if (!$this->user->getByUsernameOrEmail($username)) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Check if user is active
        if (!$this->user->is_active) {
            return ['success' => false, 'message' => 'Your account has been deactivated'];
        }

        // Verify password
        if (!$this->user->verifyPassword($password)) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Create session
        $_SESSION['user_id'] = $this->user->user_id;
        $_SESSION['username'] = $this->user->username;
        $_SESSION['email'] = $this->user->email;
        $_SESSION['user_type'] = $this->user->user_type;
        $_SESSION['last_activity'] = time();

        // Get profile data based on user type
        $profileData = null;
        if ($this->user->user_type === 'member') {
            $profileData = $this->member->getByUserId($this->user->user_id);
            $_SESSION['member_id'] = $profileData['member_id'];
            $_SESSION['full_name'] = $profileData['first_name'] . ' ' . $profileData['last_name'];
        } elseif ($this->user->user_type === 'coach') {
            $profileData = $this->coach->getByUserId($this->user->user_id);
            $_SESSION['coach_id'] = $profileData['coach_id'];
            $_SESSION['full_name'] = $profileData['first_name'] . ' ' . $profileData['last_name'];
        } elseif ($this->user->user_type === 'admin') {
            $_SESSION['full_name'] = 'Administrator';
        }

        return ['success' => true, 'message' => 'Login successful', 'user_type' => $this->user->user_type];
    }

    // Logout user
    public function logout() {
        // Destroy session
        session_unset();
        session_destroy();
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // Get current user data
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userData = [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'user_type' => $_SESSION['user_type'],
            'full_name' => isset($_SESSION['full_name']) ? $_SESSION['full_name'] : ''
        ];

        if ($_SESSION['user_type'] === 'member') {
            $userData['member_id'] = isset($_SESSION['member_id']) ? $_SESSION['member_id'] : null;
        } elseif ($_SESSION['user_type'] === 'coach') {
            $userData['coach_id'] = isset($_SESSION['coach_id']) ? $_SESSION['coach_id'] : null;
        }

        return $userData;
    }

    // Require login (redirect if not logged in)
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Please login to access this page';
            header('Location: ' . BASE_URL . 'views/auth/login.php');
            exit();
        }
    }

    // Require specific user type
    public function requireUserType($requiredType) {
        $this->requireLogin();

        if ($_SESSION['user_type'] !== $requiredType && $_SESSION['user_type'] !== 'admin') {
            $_SESSION['error'] = 'Access denied. You do not have permission to access this page.';
            header('Location: ' . BASE_URL . 'index.php');
            exit();
        }
    }

    // Update password
    public function updatePassword($currentPassword, $newPassword, $confirmPassword) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'User not logged in'];
        }

        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'New passwords do not match'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
        }

        // Get current user data
        $this->user->getById($_SESSION['user_id']);

        // Verify current password
        if (!$this->user->verifyPassword($currentPassword)) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Update password
        if ($this->user->updatePassword($newPassword)) {
            return ['success' => true, 'message' => 'Password updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update password'];
        }
    }

    // Update profile
    public function updateProfile($profileData) {
        if (!$this->isLoggedIn()) {
            return ['success' => false, 'message' => 'User not logged in'];
        }

        // Update based on user type
        if ($_SESSION['user_type'] === 'member') {
            $this->member->member_id = $_SESSION['member_id'];
            $this->member->first_name = $profileData['first_name'];
            $this->member->last_name = $profileData['last_name'];
            $this->member->phone = isset($profileData['phone']) ? $profileData['phone'] : '';

            if ($this->member->update()) {
                $_SESSION['full_name'] = $profileData['first_name'] . ' ' . $profileData['last_name'];
                return ['success' => true, 'message' => 'Profile updated successfully'];
            }
        } elseif ($_SESSION['user_type'] === 'coach') {
            $this->coach->coach_id = $_SESSION['coach_id'];
            $this->coach->first_name = $profileData['first_name'];
            $this->coach->last_name = $profileData['last_name'];
            $this->coach->specialization = isset($profileData['specialization']) ? $profileData['specialization'] : '';
            $this->coach->phone = isset($profileData['phone']) ? $profileData['phone'] : '';

            if ($this->coach->update()) {
                $_SESSION['full_name'] = $profileData['first_name'] . ' ' . $profileData['last_name'];
                return ['success' => true, 'message' => 'Profile updated successfully'];
            }
        }

        return ['success' => false, 'message' => 'Failed to update profile'];
    }

    // Get user profile
    public function getProfile() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        if ($_SESSION['user_type'] === 'member') {
            return $this->member->getByUserId($_SESSION['user_id']);
        } elseif ($_SESSION['user_type'] === 'coach') {
            return $this->coach->getByUserId($_SESSION['user_id']);
        }

        return null;
    }
}
?>
