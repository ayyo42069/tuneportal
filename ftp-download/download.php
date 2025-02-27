<?php
include 'config.php';
require_auth();

if (!isset($_GET['file_id'])) {
    header("Location: files.php");
    exit();
}

$fileId = (int)$_GET['file_id'];

// Check file ownership and permissions
$stmt = $conn->prepare("SELECT * FROM files WHERE id = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file || ($file['user_id'] !== $_SESSION['user_id'] && $_SESSION['role'] !== 'admin')) {
    log_error("Unauthorized download attempt", "WARNING", [
        'file_id' => $fileId,
        'user_id' => $_SESSION['user_id']
    ]);
    $_SESSION['error'] = "You don't have permission to download this file";
    header("Location: files.php");
    exit();
}

// Get current version file path
$stmt = $conn->prepare("
    SELECT * FROM file_versions 
    WHERE file_id = ? AND version = ?
");
$stmt->bind_param("ii", $fileId, $file['current_version']);
$stmt->execute();
$version = $stmt->get_result()->fetch_assoc();

if ($version) {
    // Log download
    $stmt = $conn->prepare("
        INSERT INTO file_download_log (file_id, version_id, user_id, user_ip) 
        VALUES (?, ?, ?, ?)
    ");
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("iiis", $fileId, $version['id'], $_SESSION['user_id'], $user_ip);
    $stmt->execute();

    // Serve file
    $file_path = __DIR__ . '/uploads/' . $version['file_path'];
    if (file_exists($file_path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($version['file_path']) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        // Add file hash verification before download
        $stored_hash = $version['file_hash'];
        $current_hash = hash_file('sha256', $file_path);
        
        if ($stored_hash !== $current_hash) {
            error_log("File integrity check failed for file ID: $fileId");
            $_SESSION['error'] = "File integrity check failed";
            header("Location: files.php");
            exit();
        }
        exit();
    }
}

$_SESSION['error'] = "File not found";
header("Location: files.php");
exit();