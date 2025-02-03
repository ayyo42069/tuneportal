<?php
include 'config.php';
require_auth(true); // Admin only

// Handle file processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    try {
        // Validate CSRF token
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception("Security verification failed");
        }

        $file_id = filter_input(INPUT_POST, 'file_id', FILTER_VALIDATE_INT);
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        
        if (!$file_id || !$user_id) {
            throw new Exception("Invalid file or user ID");
        }

        // Validate file upload
        if (empty($_FILES['processed_file']['name'])) {
            throw new Exception("No file uploaded");
        }

        $file = $_FILES['processed_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($ext !== 'bin') {
            throw new Exception("Only .bin files accepted");
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception("File size exceeds 5MB limit");
        }

        // Get current version
        $stmt = $conn->prepare("SELECT current_version FROM files WHERE id = ?");
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$current) {
            throw new Exception("File not found");
        }
        
        $new_version = $current['current_version'] + 1;
        
        // Prepare upload directory
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
        
        // Generate safe filename
        $filename = sprintf("processed_%d_v%d.bin", $file_id, $new_version);
        $targetPath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to save processed file");
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update files table
            $stmt = $conn->prepare("UPDATE files SET status = 'processed', current_version = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_version, $file_id);
            $stmt->execute();
            
            // Insert file version
            $stmt = $conn->prepare("INSERT INTO file_versions (file_id, version, file_path, notes) VALUES (?, ?, ?, ?)");
            $notes = $_POST['version_notes'] ?? 'Processed by admin';
            $stmt->bind_param("iiss", $file_id, $new_version, $filename, $notes);
            $stmt->execute();
            
            // Create notification
            $message = "Your file '{$file['title']}' has been processed!";
            $link = "file_details.php?id={$file_id}";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $message, $link);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success'] = "File processed successfully. New version: v{$new_version}";
        } catch (Exception $e) {
            $conn->rollback();
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            throw $e;
        }

        header("Location: process.php");
        exit();
    } catch (Exception $e) {
        error_log("Processing error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header("Location: process.php");
        exit();
    }
}

// Display messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Get files with versions
try {
    $stmt = $conn->prepare("
        SELECT f.*, u.username, fv.file_path 
        FROM files f
        JOIN users u ON f.user_id = u.id
        LEFT JOIN file_versions fv ON f.id = fv.file_id AND f.current_version = fv.version
        ORDER BY f.created_at DESC
    ");
    $stmt->execute();
    $files = $stmt->get_result();
} catch (Exception $e) {
    error_log("File listing error: " . $e->getMessage());
    $error = "Error loading files";
}

include 'header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-red-600 mb-6">Process User Files</h2>

        <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($success, ENT_QUOTES) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($error, ENT_QUOTES) ?>
        </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-red-50">
                        <th class="p-3 text-left">File</th>
                        <th class="p-3 text-left">User</th>
                        <th class="p-3 text-left">Current Version</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($file = $files->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-3">
                            <div class="font-semibold"><?= htmlspecialchars($file['title'], ENT_QUOTES) ?></div>
                            <div class="text-sm text-gray-600"><?= htmlspecialchars($file['car_model'], ENT_QUOTES) ?></div>
                        </td>
                        <td class="p-3"><?= htmlspecialchars($file['username'], ENT_QUOTES) ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                v<?= htmlspecialchars($file['current_version'], ENT_QUOTES) ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <?php if(!empty($file['file_path'])): ?>
                                <a href="uploads/<?= htmlspecialchars($file['file_path'], ENT_QUOTES) ?>" 
                                   class="text-blue-600 hover:text-blue-800"
                                   download>
                                    Download
                                </a>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                <?php echo csrf_input_field(); ?>
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                    <input type="hidden" name="user_id" value="<?= $file['user_id'] ?>">
                                    
                                    <div class="flex flex-col gap-2">
                                        <input type="file" name="processed_file" 
                                               class="text-sm" accept=".bin" required>
                                        <input type="text" name="version_notes" 
                                               placeholder="Version notes"
                                               class="text-sm p-1 border rounded">
                                    </div>
                                    
                                    <button type="submit" 
                                            class="bg-red-600 text-white px-3 py-1.5 rounded hover:bg-red-700">
                                        Upload Processed
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
