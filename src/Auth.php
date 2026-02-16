<?php
/**
 * Authentication class
 */

class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Register a new citizen
     */
    public function register($name, $email, $password, $ward_id = null) {
        try {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            
            // Check if email exists
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user
            $stmt = $this->conn->prepare("INSERT INTO users (name, email, password_hash, default_ward_id, user_type) VALUES (?, ?, ?, ?, ?)");
            $user_type = 'citizen';
            $stmt->bind_param("sssss", $name, $email, $password_hash, $ward_id, $user_type);
            
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                // Session setup is done on the calling page (signup.php) for immediate redirect
                return ['success' => true, 'message' => 'Registration successful', 'user_id' => $user_id];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    
    public function login($email, $password) {
        try {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            
            $stmt = $this->conn->prepare("SELECT id, password_hash, name, user_type, is_blocked FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            
            $user = $result->fetch_assoc();
            
            if ($user['is_blocked']) {
                return ['success' => false, 'message' => 'Your account has been blocked'];
            }
            
            if (password_verify($password, $user['password_hash'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_name'] = $user['name'];
                
                // Update last login
                $stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                
                return ['success' => true, 'message' => 'Login successful', 'user_id' => $user['id'], 'user_type' => $user['user_type']];
            } else {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Admin login
     * * FIXED: Changed LEFT JOIN to INNER JOIN and added ar.role IS NOT NULL 
     * to enforce strict admin role verification for the admin panel.
     */
    public function adminLogin($email, $password) {
        try {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            
            $stmt = $this->conn->prepare("
                SELECT u.id, u.password_hash, u.name, u.user_type, u.is_blocked, ar.role 
                FROM users u
                INNER JOIN admin_roles ar ON u.id = ar.user_id 
                WHERE u.email = ? AND u.user_type = 'admin' AND ar.role IS NOT NULL
            ");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // This will catch: 1) Wrong email/non-existent user 2) User is not marked as 'admin' in users table 
                // 3) User exists but has no corresponding role in admin_roles (because of the INNER JOIN)
                return ['success' => false, 'message' => 'Invalid admin credentials'];
            }
            
            $user = $result->fetch_assoc();
            
            if ($user['is_blocked']) {
                return ['success' => false, 'message' => 'Your account has been blocked'];
            }
            
            if (password_verify($password, $user['password_hash'])) {
                session_start();
                
                // Set standard user sessions (Crucial for page checks like !isset($_SESSION['user_id']))
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_name'] = $user['name'];

                // Set admin-specific sessions
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_role'] = $user['role'];
                
                // Update last login
                $stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();
                
                return ['success' => true, 'message' => 'Admin login successful', 'admin_id' => $user['id'], 'role' => $user['role']];
            } else {
                // Returns this message if password_verify fails
                return ['success' => false, 'message' => 'Invalid admin credentials'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Debug admin login (for troubleshooting)
     */
    public function debugAdminLogin($email) {
        try {
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);
            
            // Check user exists
            $stmt = $this->conn->prepare("SELECT id, user_type FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            if (!$user) {
                return ['status' => 'User not found'];
            }
            
            // Check admin role exists
            $stmt = $this->conn->prepare("SELECT role FROM admin_roles WHERE user_id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $role = $stmt->get_result()->fetch_assoc();
            
            return [
                'status' => 'Found',
                'user_type' => $user['user_type'],
                'has_admin_role' => $role ? 'Yes' : 'No',
                'role' => $role['role'] ?? null
            ];
        } catch (Exception $e) {
            return ['status' => 'Error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        // Only start session if one is not active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if admin is logged in
     */
    public static function isAdminLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['admin_id']);
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        session_start();
        session_destroy();
        return true;
    }
    
    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current admin ID
     */
    public static function getCurrentAdminId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['admin_id'] ?? null;
    }
}