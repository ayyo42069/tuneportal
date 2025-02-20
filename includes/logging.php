<?php
function log_error($message, $severity = "INFO", $context = []) {
    global $conn;
    
    // Add stack trace for better debugging
    if ($severity === "ERROR" || $severity === "CRITICAL") {
        $context['stack_trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }
    
    // Add request information
    $context['request'] = [
        'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $context_json = json_encode($context);
    
    // Use transaction to ensure log entry is saved
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO error_log (message, severity, user_id, context) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $message, $severity, $_SESSION['user_id'] ?? null, $context_json);
        $stmt->execute();
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Failed to log error: " . $e->getMessage());
    }
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