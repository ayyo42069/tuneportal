<?php
include 'config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = "Invalid CSRF token";
    header("Location: file_details.php?id=" . $_POST['file_id']);
    exit();
}

$file_id = (int)$_POST['file_id'];
$user_id = $_SESSION['user_id'];
$message = trim($_POST['message']);

try {
    $conn->begin_transaction();

    // Update file status to pending
    $stmt = $conn->prepare("UPDATE files SET status = 'pending', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();

    // Insert into file_transactions
    $stmt = $conn->prepare("
        INSERT INTO file_transactions 
        (file_id, user_id, action_type, notes, created_at) 
        VALUES (?, ?, 'update_requested', ?, NOW())
    ");
    $stmt->bind_param("iis", $file_id, $user_id, $message);
    $stmt->execute();

    // Notify admins
    $stmt = $conn->prepare("
        INSERT INTO notifications 
        (user_id, message, link, created_at, is_read) 
        SELECT 
            id,
            CONCAT('New update request for file #', ?, ': ', ?),
            CONCAT('admin_files.php?id=', ?),
            NOW(),
            0
        FROM users 
        WHERE role = 'admin'
    ");
    $stmt->bind_param("isi", $file_id, $message, $file_id);
    $stmt->execute();

    $conn->commit();
    $_SESSION['success'] = "Update request submitted successfully";

} catch (Exception $e) {
    $conn->rollback();
    log_error("Update request failed", "ERROR", [
        'file_id' => $file_id,
        'error' => $e->getMessage()
    ]);
    $_SESSION['error'] = "Failed to submit update request";
}

header("Location: file_details.php?id=" . $file_id);
exit();