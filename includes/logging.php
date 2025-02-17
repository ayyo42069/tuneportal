<?php
function log_error($message, $severity = "ERROR", $context = []) {
    global $conn;
    
    // Validate severity
    $allowed_severities = ['ERROR', 'WARNING', 'INFO', 'CRITICAL'];
    $severity = strtoupper($severity);
    if (!in_array($severity, $allowed_severities)) {
        $severity = 'ERROR';
    }
    
    // Add error details to context
    if (error_get_last()) {
        $context['php_error'] = error_get_last();
    }
    
    // Add request information
    $context['request_uri'] = $_SERVER['REQUEST_URI'] ?? '';
    $context['request_method'] = $_SERVER['REQUEST_METHOD'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO error_log (severity, message, context, user_id) VALUES (?, ?, ?, ?)");
    $context_json = json_encode($context);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $stmt->bind_param("sssi", $severity, $message, $context_json, $user_id);
    $stmt->execute();
}

// Add a function to check database connection
function check_db_connection() {
    global $conn;
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    return true;
}