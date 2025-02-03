<?php
include 'config.php';
require_auth();

// Define constants
define('UPLOAD_DIR', __DIR__ . '/uploads/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction for data consistency
    $conn->begin_transaction();
    
    try {
        // Validate file
        $file = $_FILES['bin_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        // Check file extension and MIME type
        if ($ext !== 'bin' || mime_content_type($file['tmp_name']) !== 'application/octet-stream') {
            $_SESSION['error'] = "Only .bin files are allowed";
            header("Location: dashboard.php");
            exit();
        }

        // Calculate total credits needed
        $totalCredits = 0;
        if (isset($_POST['tuning_options'])) {
            $options = implode(",", array_map('intval', $_POST['tuning_options']));
            $creditQuery = $conn->prepare("SELECT SUM(credit_cost) AS total FROM tuning_options WHERE id IN ($options)");
            $creditQuery->execute();
            $creditQuery->bind_result($totalCredits);
            $creditQuery->fetch();
            $creditQuery->close();
        }

        // Check user credits
        $stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($userCredits);
        $stmt->fetch();
        $stmt->close();

        if ($userCredits < $totalCredits) {
            $_SESSION['error'] = "Insufficient credits";
            header("Location: dashboard.php");
            exit();
        }

        // Create file record
        $stmt = $conn->prepare("INSERT INTO files (user_id, title, description, car_model) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $_POST['title'], $_POST['description'], $_POST['car_model']);
        $stmt->execute();
        $fileId = $stmt->insert_id;
        $stmt->close();

        // Store file
        $filename = "file_{$fileId}_v1.bin";
        if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)) {
            throw new Exception("Failed to move uploaded file.");
        }

        // Create version record
        $stmt = $conn->prepare("INSERT INTO file_versions (file_id, version, file_path) VALUES (?, ?, ?)");
        $version = 1;
        $stmt->bind_param("iis", $fileId, $version, $filename);
        $stmt->execute();
        $stmt->close();

        // Deduct credits and log transaction
        $stmt = $conn->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
        $stmt->bind_param("ii", $totalCredits, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        // Log credit transaction with negative amount
        $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'file_upload', ?)");
        $description = "Credits used for file upload: " . htmlspecialchars($_POST['title']);
        $negativeAmount = -$totalCredits; // Convert to negative for deduction
        $stmt->bind_param("iis", $_SESSION['user_id'], $negativeAmount, $description);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "File uploaded successfully";
        header("Location: file_details.php?id=$fileId");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "An error occurred during file upload: " . htmlspecialchars($e->getMessage());
        header("Location: dashboard.php");
        exit();
    }
}
