<?php
include 'config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    $_SESSION['error'] = "Invalid request";
    header("Location: files.php");
    exit();
}

$fileId = (int)$_POST['file_id'];
$message = trim($_POST['message']);
$userId = $_SESSION['user_id'];

// Verify file exists and user has access
$stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND (user_id = ? OR ? IN (SELECT user_id FROM file_shares WHERE file_id = ?))");
$stmt->bind_param("iiii", $fileId, $userId, $userId, $fileId);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
    $_SESSION['error'] = "File not found or access denied";
    header("Location: files.php");
    exit();
}

// Check if there's already a pending request
$stmt = $conn->prepare("SELECT id FROM file_update_requests WHERE file_id = ? AND user_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $fileId, $userId);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = "You already have a pending update request for this file";
    header("Location: file_details.php?id=" . $fileId);
    exit();
}

// Create update request
$stmt = $conn->prepare("INSERT INTO file_update_requests (file_id, user_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $fileId, $userId, $message);

if ($stmt->execute()) {
    // Log the action
    $stmt = $conn->prepare("INSERT INTO file_transactions (file_id, user_id, action_type, details) VALUES (?, ?, 'update_requested', ?)");
    $action = "Update requested: " . substr($message, 0, 100) . (strlen($message) > 100 ? "..." : "");
    $stmt->bind_param("iis", $fileId, $userId, $action);
    $stmt->execute();

    $_SESSION['success'] = "Update request submitted successfully";
} else {
    $_SESSION['error'] = "Failed to submit update request";
}

header("Location: file_details.php?id=" . $fileId);
exit();

