<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
// Include encryption functions
require_once __DIR__ . '/includes/encryption.php';

// Load environment variables
load_env();

// Set secure session cookie parameters BEFORE starting the session
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

// Load environment variables
load_env();

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

function log_error($message, $severity = 'ERROR', $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest';
    $request_uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
    
    $log_entry = sprintf(
        "[%s] [%s] [User: %s] [URL: %s] %s %s\n",
        $timestamp,
        $severity,
        $user_id,
        $request_uri,
        $message,
        !empty($context) ? json_encode($context) : ''
    );
    
    error_log($log_entry, 3, ERROR_LOG_PATH);
}

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

function encrypt_file($source, $destination) {
    try {
        $content = file_get_contents($source);
        if ($content === false) {
            throw new Exception("Could not read source file");
        }

        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($content, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
        
        if ($encrypted === false) {
            throw new Exception("Encryption failed");
        }

        $final = base64_encode($iv) . ':' . $encrypted;
        if (file_put_contents($destination, $final) === false) {
            throw new Exception("Could not write encrypted file");
        }

        return true;
    } catch (Exception $e) {
        log_error("File encryption failed", "ERROR", ['error' => $e->getMessage()]);
        return false;
    }
}

function decrypt_file($source, $destination) {
    try {
        $content = file_get_contents($source);
        if ($content === false) {
            throw new Exception("Could not read encrypted file");
        }

        list($iv, $encrypted) = explode(':', $content);
        $iv = base64_decode($iv);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', ENCRYPTION_KEY, 0, $iv);
        if ($decrypted === false) {
            throw new Exception("Decryption failed");
        }

        if (file_put_contents($destination, $decrypted) === false) {
            throw new Exception("Could not write decrypted file");
        }

        return true;
    } catch (Exception $e) {
        log_error("File decryption failed", "ERROR", ['error' => $e->getMessage()]);
        return false;
    }
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_input_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
?>