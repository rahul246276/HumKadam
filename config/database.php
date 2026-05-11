<?php
/**
 * Database Configuration
 * HumKadam Matrimonial Contact Management System
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'humkadam_contacts');
define('DB_CHARSET', 'utf8mb4');

// Admin panel settings
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'humkadam123');

// Application settings
define('APP_NAME', 'HumKadam Matrimonial Services');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://humkadam.com');

// Security settings
define('HASH_COST', 12);
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// Email settings (for future notifications)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@humkadam.com');
define('SMTP_FROM_NAME', 'HumKadam Matrimonial Services');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('Asia/Kolkata');

/**
 * Create database connection
 * @return mysqli
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }
    
    $conn->set_charset(DB_CHARSET);
    return $conn;
}

/**
 * Secure password hashing
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * Verify password
 * @param string $password
 * @param string $hash
 * @return boolean
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize input
 * @param mixed $input
 * @return mixed
 */
function sanitizeInput($input) {
    if (is_string($input)) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return boolean
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
