<?php
include 'config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction for data consistency
    $conn->begin_transaction();
    
    try {
        // Validate file
        $file = $_FILES['bin_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if ($ext !== 'bin') {
            $_SESSION['error'] = "Only .bin files are allowed";
            header("Location: dashboard.php");
            exit();
        }

        // Calculate total credits needed
        $totalCredits = 0;
        if(isset($_POST['tuning_options'])) {
            $options = implode(",", array_map('intval', $_POST['tuning_options']));
            $creditQuery = $conn->query("SELECT SUM(credit_cost) AS total FROM tuning_options WHERE id IN ($options)");
            $totalCredits = $creditQuery->fetch_assoc()['total'];
        }

        // Check user credits
        $user = $conn->query("SELECT credits FROM users WHERE id = {$_SESSION['user_id']}")->fetch_assoc();
        if ($user['credits'] < $totalCredits) {
            $_SESSION['error'] = "Insufficient credits";
            header("Location: dashboard.php");
            exit();
        }

        // Create file record
        $stmt = $conn->prepare("INSERT INTO files (user_id, title, description, car_model) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $_POST['title'], $_POST['description'], $_POST['car_model']);
        $stmt->execute();
        $fileId = $stmt->insert_id;

        // Store file
        $uploadDir = __DIR__ . '/uploads/';
        $filename = "file_{$fileId}_v1.bin";
        move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

        // Create version record
        $conn->query("INSERT INTO file_versions (file_id, version, file_path) 
                     VALUES ($fileId, 1, '$filename')");

        // Deduct credits and log transaction
        $conn->query("UPDATE users SET credits = -credits - $totalCredits WHERE id = {$_SESSION['user_id']}");
        
        // Log credit transaction
        $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) 
                              VALUES (?, ?, 'file_upload', ?)");
        $description = "Credits used for file upload: " . htmlspecialchars($_POST['title']);
        $stmt->bind_param("iis", $_SESSION['user_id'], $totalCredits, $description);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "File uploaded successfully";
        header("Location: file_details.php?id=$fileId");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "An error occurred during file upload";
        header("Location: dashboard.php");
        exit();
    }
}