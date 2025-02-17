<?php
include 'config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verify_csrf_token($_POST['csrf_token'])) {
            throw new Exception("Invalid CSRF token");
        }

        // Validate file
        list($valid, $message) = validate_file($_FILES['bin_file']);
        if (!$valid) {
            throw new Exception($message);
        }

        $conn->begin_transaction();

        // Calculate total credits
        $totalCredits = 0;
        if(isset($_POST['tuning_options'])) {
            $options = array_map('intval', $_POST['tuning_options']);
            $placeholders = str_repeat('?,', count($options) - 1) . '?';
            $stmt = $conn->prepare("SELECT SUM(credit_cost) AS total FROM tuning_options WHERE id IN ($placeholders)");
            $stmt->bind_param(str_repeat('i', count($options)), ...$options);
            $stmt->execute();
            $totalCredits = $stmt->get_result()->fetch_assoc()['total'];
        }

        // Check user credits
        $stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user['credits'] < $totalCredits) {
            throw new Exception("Insufficient credits");
        }

        // Create file record
        $stmt = $conn->prepare("INSERT INTO files (user_id, title, description, car_model) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $_POST['title'], $_POST['description'], $_POST['car_model']);
        $stmt->execute();
        $fileId = $stmt->insert_id;

        $uploadDir = __DIR__ . '/uploads/';
        $filename = "file_{$fileId}_v1.bin";
        
        // Encrypt and store the file
        if (!encrypt_file($_FILES['bin_file']['tmp_name'], $uploadDir . $filename)) {
            throw new Exception("Failed to encrypt file");
        }

        // Calculate file hash
        $file_hash = hash_file('sha256', $uploadDir . $filename);

        // Create version record with hash
        $stmt = $conn->prepare("INSERT INTO file_versions (file_id, version, file_path, file_hash) VALUES (?, 1, ?, ?)");
        $stmt->bind_param("iss", $fileId, $filename, $file_hash);
        $stmt->execute();

        // Deduct credits and log transaction
        $stmt = $conn->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
        $stmt->bind_param("ii", $totalCredits, $_SESSION['user_id']);
        $stmt->execute();
        
        // Log credit transaction
        $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'file_upload', ?)");
        $description = "Credits used for file upload: " . $_POST['title'];
        $negativeAmount = -$totalCredits;
        $stmt->bind_param("iis", $_SESSION['user_id'], $negativeAmount, $description);
        $stmt->execute();

        $conn->commit();

        log_error("File uploaded successfully", "INFO", [
            'file_id' => $fileId,
            'user_id' => $_SESSION['user_id'],
            'credits_used' => $totalCredits
        ]);

        $_SESSION['success'] = "File uploaded successfully";
        header("Location: file_details.php?id=$fileId");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        log_error("File upload failed", "ERROR", [
            'error' => $e->getMessage(),
            'user_id' => $_SESSION['user_id']
        ]);
        $_SESSION['error'] = "Upload failed: " . $e->getMessage();
        header("Location: dashboard.php");
        exit();
    }
}