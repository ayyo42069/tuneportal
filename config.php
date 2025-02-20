<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define constants
define('ENVIRONMENT', 'development');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['bin', 'jpg', 'jpeg', 'png', 'gif']);
define('ERROR_LOG_PATH', __DIR__ . '/logs/error.log');
function get_device_type($user_agent) {
    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($user_agent))) {
        return 'Tablet';
    }
    
    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($user_agent))) {
        return 'Mobile';
    }
    
    if (preg_match('/(ipod|iphone|ipad)/i', strtolower($user_agent))) {
        return 'iOS Device';
    }
    
    if (preg_match('/macintosh|mac os x/i', $user_agent)) {
        return 'Mac';
    }
    
    if (preg_match('/windows|win32/i', $user_agent)) {
        return 'Windows';
    }
    
    if (preg_match('/linux/i', $user_agent)) {
        return 'Linux';
    }
    
    return 'Unknown';
}

// Load environment variables from .env file
function load_env() {
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) {
        die('.env file not found');
    }

    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load environment variables
load_env();

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Include required files
require_once __DIR__ . '/includes/encryption.php';
require_once __DIR__ . '/includes/logging.php';
require_once __DIR__ . '/includes/file_handler.php';
// Add after other requires
require_once __DIR__ . '/includes/language.php';

// Set secure session cookie parameters and start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookieParams['path'],
        'domain'   => $cookieParams['domain'],
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Create a new MySQLi connection using environment variables
$conn = new mysqli(
    getenv('DB_HOST'),
    getenv('DB_USER'),
    getenv('DB_PASS'),
    getenv('DB_NAME')
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

function handle_db_error($query, $error) {
    $context = [
        'query' => $query,
        'error' => $error,
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
    ];
    
    log_error("Database error: $error", 'ERROR', $context);
    
    if (ENVIRONMENT === 'development') {
        throw new Exception("Database error: $error\nQuery: $query");
    } else {
        throw new Exception("A database error occurred. Please try again later.");
    }
}

function sanitize($data) {
    if (is_array($data)) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = sanitize($value);
        }
        return $sanitized;
    } else {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

function require_auth($admin = false) {
    if (!isset($_SESSION['user_id'])) {
        log_error("Unauthorized access attempt", "WARNING");
        header("Location: login.php");
        exit();
    }
    if ($admin && $_SESSION['role'] !== 'admin') {
        log_error("Unauthorized admin access attempt", "WARNING", ['user_id' => $_SESSION['user_id']]);
        header("HTTP/1.1 403 Forbidden");
        exit("Admin access required");
    }
}

function validate_file($file) {
    if ($file['size'] > MAX_FILE_SIZE) {
        log_error("File size exceeds limit", "WARNING", ['size' => $file['size']]);
        return [false, "File size must not exceed " . (MAX_FILE_SIZE / 1024 / 1024) . "MB"];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        log_error("Invalid file extension", "WARNING", ['extension' => $ext]);
        return [false, "Only " . implode(', ', ALLOWED_EXTENSIONS) . " files are allowed"];
    }

    return [true, ""];
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !$token) {
        log_error("CSRF token missing", "WARNING", [
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'page' => $_SERVER['PHP_SELF'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        return false;
    }

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        log_error("CSRF token mismatch", "WARNING", [
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'provided_token' => $token,
            'page' => $_SERVER['PHP_SELF'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        return false;
    }

    return true;
}

function csrf_input_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
// Add after successful authentication
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT dark_mode FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($pref = $result->fetch_assoc()) {
        $_SESSION['dark_mode'] = $pref['dark_mode'];
    }
}
// Register shutdown function to handle fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        log_error("Fatal Error", "CRITICAL", $error);
    }
});
?>