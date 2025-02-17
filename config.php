<?php
// Set secure session cookie parameters BEFORE starting the session
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => 0,           // Session cookie lasts until the browser is closed
    'path'     => $cookieParams['path'],
    'domain'   => $cookieParams['domain'],
    'secure'   => true,        // Send cookie only over HTTPS
    'httponly' => true,        // Inaccessible to JavaScript
    'samesite' => 'Lax'         // Helps mitigate CSRF
]);
session_start();

// Database credentials
$host = "localhost";
$user = "";
$pass = "";
$db   = "";

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * Sanitize input data recursively.
 *
 * For string values:
 * - Trims whitespace,
 * - Strips HTML and PHP tags,
 * - Converts special characters to HTML entities using UTF-8 encoding.
 *
 * For arrays, it sanitizes each element recursively.
 *
 * @param mixed $data The data to sanitize.
 * @return mixed The sanitized data.
 */
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

/**
 * Require user authentication.
 * Optionally restricts access to admin users.
 *
 * @param bool $admin Set to true to require admin privileges.
 */
function require_auth($admin = false) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    if ($admin && $_SESSION['role'] !== 'admin') {
        header("HTTP/1.1 403 Forbidden");
        exit("Admin access required");
    }
}

/**
 * Generate a CSRF token, store it in the session if not present, and return it.
 *
 * @return string CSRF token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        // Generate a secure token (32 bytes = 64 hex characters)
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the provided CSRF token against the token stored in the session.
 *
 * @param string $token The token submitted via the form.
 * @return bool True if valid; false otherwise.
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Returns the HTML for a hidden input field containing the CSRF token.
 *
 * @return string HTML input element with CSRF token.
 */
function csrf_input_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
?>

