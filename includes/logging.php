<?php
function log_error($message, $severity = "INFO", $context = []) {
    global $conn;
    
    // Validate severity
    $valid_severities = ['INFO', 'WARNING', 'ERROR', 'CRITICAL'];
    $severity = in_array(strtoupper($severity), $valid_severities) ? strtoupper($severity) : 'INFO';
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $context_json = json_encode($context);
    
    // Create variables for bind_param
    $stmt = $conn->prepare("INSERT INTO error_log (message, severity, user_id, context) VALUES (?, ?, ?, ?)");
    $null_user = $user_id === null ? null : $user_id;
    
    // Bind parameters by reference
    $stmt->bind_param("ssis", $message, $severity, $null_user, $context_json);
    
    try {
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (Exception $e) {
        error_log("Logging error: " . $e->getMessage());
        return false;
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