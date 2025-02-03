<?php
include 'config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    $file_id = (int)$_POST['file_id'];
    $message = sanitize($_POST['message']);
    
    // Verify file ownership
    $stmt = $conn->prepare("SELECT id FROM files WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $file_id, $_SESSION['user_id']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 1) {
        $stmt = $conn->prepare("INSERT INTO update_requests (file_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $file_id, $_SESSION['user_id'], $message);
        
        if ($stmt->execute()) {
            // Notify admin
            $admin_msg = "New update request for file #$file_id";
            $conn->query("INSERT INTO notifications (user_id, message, link) 
                         VALUES (1, '$admin_msg', 'admin_requests.php')");
            
            $_SESSION['success'] = "Update request submitted successfully";
        } else {
            $_SESSION['error'] = "Failed to submit request";
        }
    } else {
        $_SESSION['error'] = "Invalid file";
    }
    
    header("Location: file_details.php?id=$file_id");
    exit();
}