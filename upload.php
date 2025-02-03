<?php
include 'config.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception("Security verification failed");
        }

        // Validate file
        $file = $_FILES['bin_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($ext !== 'bin') {
            throw new Exception("Only .bin files are allowed");
        }

        // Calculate total credits needed
        $totalCredits = 0;
        if (isset($_POST['tuning_options'])) {
            $options = array_map('intval', $_POST['tuning_options']);
            $optionsList = implode(",", $options);
            
            $creditQuery = $conn->query("SELECT SUM(credit_cost) AS total FROM tuning_options WHERE id IN ($optionsList)");
            $totalCredits = $creditQuery->fetch_assoc()['total'] ?? 0;
        }

        // Check user credits
        $stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user['credits'] < $totalCredits) {
            throw new Exception("Insufficient credits");
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Create file record
            $stmt = $conn->prepare("INSERT INTO files (user_id, title, description, car_model) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $_SESSION['user_id'], $_POST['title'], $_POST['description'], $_POST['car_model']);
            $stmt->execute();
            $fileId = $stmt->insert_id;
            $stmt->close();

            // Store file
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = "file_{$fileId}_v1.bin";
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                throw new Exception("Failed to save uploaded file");
            }

            // Create version record
            $stmt = $conn->prepare("INSERT INTO file_versions (file_id, version, file_path) VALUES (?, 1, ?)");
            $stmt->bind_param("is", $fileId, $filename);
            $stmt->execute();
            $stmt->close();

            // Deduct credits and log transaction
            if ($totalCredits > 0) {
                // Update user credits
                $stmt = $conn->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
                $stmt->bind_param("ii", $totalCredits, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                // Log credit transaction
                $stmt = $conn->prepare("
                    INSERT INTO credit_transactions (user_id, amount, type, description)
                    VALUES (?, ?, 'file_upload', ?)
                ");
                $description = "File upload #{$fileId}";
                $stmt->bind_param("iis", $_SESSION['user_id'], $totalCredits, $description);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            $_SESSION['success'] = "File uploaded successfully";
            header("Location: file_details.php?id=$fileId");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            if (isset($filename) && file_exists($uploadDir . $filename)) {
                unlink($uploadDir . $filename);
            }
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Upload error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: dashboard.php");
        exit();
    }
}