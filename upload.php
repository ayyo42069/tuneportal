<?php
include 'config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verify_csrf_token($_POST['csrf_token'])) {
            throw new Exception("Invalid CSRF token");
        }

        // Validate required fields
        $required_fields = ['title', 'manufacturer', 'model', 'year', 'ecu_type'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate file
        if (!isset($_FILES['bin_file']) || $_FILES['bin_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("No file uploaded or upload error occurred");
        }

        list($valid, $message) = validate_file($_FILES['bin_file']);
        if (!$valid) {
            throw new Exception($message);
        }

        // Simplified file type validation
        $file_extension = strtolower(pathinfo($_FILES['bin_file']['name'], PATHINFO_EXTENSION));
        if ($file_extension !== 'bin') {
            throw new Exception("Invalid file type. Only .bin files are allowed.");
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

        // Validate vehicle information
        $stmt = $conn->prepare("
            SELECT cm.name as model_name, cm.year_start, cm.year_end, et.name as ecu_name 
            FROM car_models cm 
            JOIN ecu_types et ON et.model_id = cm.id 
            WHERE cm.id = ? AND et.id = ?
        ");
        $stmt->bind_param("ii", $_POST['model'], $_POST['ecu_type']);
        $stmt->execute();
        $vehicle_info = $stmt->get_result()->fetch_assoc();

        if (!$vehicle_info) {
            throw new Exception("Invalid vehicle information");
        }

        if ($_POST['year'] < $vehicle_info['year_start'] || $_POST['year'] > $vehicle_info['year_end']) {
            throw new Exception("Invalid year for selected model");
        }

        // Create file record with vehicle information
        $stmt = $conn->prepare("
            INSERT INTO files (
                user_id, title, description, manufacturer_id, model_id, 
                year, ecu_type_id, status, current_version
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 1)
        ");
        $stmt->bind_param(
            "issiiii", 
            $_SESSION['user_id'], 
            $_POST['title'], 
            $_POST['description'], 
            $_POST['manufacturer'],
            $_POST['model'],
            $_POST['year'],
            $_POST['ecu_type']
        );
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

        // Store selected tuning options
        if (!empty($_POST['tuning_options'])) {
            $stmt = $conn->prepare("
                INSERT INTO file_tuning_options (file_id, option_id)
                VALUES (?, ?)
            ");
            
            foreach ($_POST['tuning_options'] as $optionId) {
                $optionIdInt = (int)$optionId;
                $stmt->bind_param("ii", $fileId, $optionIdInt);
                $stmt->execute();
            }
        }

        // Notify admins about new file upload
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, message, link, is_read)
            SELECT id, ?, ?, 0
            FROM users
            WHERE role = 'admin'
        ");
        $notificationMsg = "New file upload requires review: " . htmlspecialchars($_POST['title']);
        $notificationLink = "admin_files.php?action=review&id=" . $fileId;
        $stmt->bind_param("ss", $notificationMsg, $notificationLink);
        $stmt->execute();

        // Deduct credits and log transaction
        $stmt = $conn->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
        $stmt->bind_param("ii", $totalCredits, $_SESSION['user_id']);
        $stmt->execute();
        
        // Log credit transaction
        $stmt = $conn->prepare("
            INSERT INTO credit_transactions (user_id, amount, type, description) 
            VALUES (?, ?, 'file_upload', ?)
        ");
        $description = "Credits used for file upload: " . $_POST['title'];
        $negativeAmount = -$totalCredits;
        $stmt->bind_param("iis", $_SESSION['user_id'], $negativeAmount, $description);
        $stmt->execute();

        $conn->commit();

        log_error("File uploaded successfully", "INFO", [
            'file_id' => $fileId,
            'user_id' => $_SESSION['user_id'],
            'credits_used' => $totalCredits,
            'vehicle_info' => [
                'manufacturer_id' => $_POST['manufacturer'],
                'model_id' => $_POST['model'],
                'year' => $_POST['year'],
                'ecu_type' => $_POST['ecu_type']
            ]
        ]);

        $_SESSION['success'] = __('file_uploaded', 'notifications');
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