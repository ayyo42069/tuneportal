<?php
function log_error($message, $severity = "ERROR", $context = []) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO error_log (severity, message, context, user_id) VALUES (?, ?, ?, ?)");
    $context_json = json_encode($context);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $stmt->bind_param("sssi", $severity, $message, $context_json, $user_id);
    $stmt->execute();
}