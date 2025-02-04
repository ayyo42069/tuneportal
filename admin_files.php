<?php
include 'config.php';
require_auth(true); // Admin only

// Handle file processing and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    // Handle deletion
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $file_id = (int)$_POST['file_id'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get all file versions to delete physical files
            $stmt = $conn->prepare("SELECT file_path FROM file_versions WHERE file_id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $versions = $stmt->get_result();
            while ($version = $versions->fetch_assoc()) {
                $filepath = __DIR__ . '/uploads/' . $version['file_path'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            $stmt->close();
            
            // Delete all associated records
            $stmt = $conn->prepare("DELETE FROM file_versions WHERE file_id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM update_requests WHERE file_id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $_SESSION['success'] = "File and all associated data deleted successfully";
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error deleting file: " . htmlspecialchars($e->getMessage());
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Handle file processing
    if (isset($_FILES['processed_file'])) {
        $file_id = (int)$_POST['file_id'];
        $user_id = (int)$_POST['user_id'];
        
        // Upload processed file
        if (!empty($_FILES['processed_file']['name'])) {
            $file = $_FILES['processed_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if ($ext === 'bin') {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Get current version
                    $stmt = $conn->prepare("SELECT current_version FROM files WHERE id = ?");
                    $stmt->bind_param("i", $file_id);
                    $stmt->execute();
                    $current = $stmt->get_result()->fetch_assoc();
                    $new_version = $current['current_version'] + 1;
                    $stmt->close();
                    
                    // Store file
                    $uploadDir = __DIR__ . '/uploads/';
                    $filename = "processed_{$file_id}_v{$new_version}.bin";
                    
                    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                        throw new Exception("Failed to upload file");
                    }
                    
                    // Update database
                    $stmt = $conn->prepare("UPDATE files SET status = 'processed', current_version = ? WHERE id = ?");
                    $stmt->bind_param("ii", $new_version, $file_id);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare("INSERT INTO file_versions (file_id, version, file_path) VALUES (?, ?, ?)");
                    $stmt->bind_param("iis", $file_id, $new_version, $filename);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Create notification
                    $message = "Your file #$file_id has been processed!";
                    $link = "file_details.php?id=$file_id";
                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $user_id, $message, $link);
                    $stmt->execute();
                    $stmt->close();
                    
                    $conn->commit();
                    $_SESSION['success'] = "File processed successfully";
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    $_SESSION['error'] = "Error processing file: " . htmlspecialchars($e->getMessage());
                    
                    // Clean up uploaded file if it exists
                    if (file_exists($uploadDir . $filename)) {
                        unlink($uploadDir . $filename);
                    }
                }
            } else {
                $_SESSION['error'] = "Only .bin files accepted";
            }
        }
    }
}

include 'header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-red-600 mb-6">Manage User Files</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-red-50">
                        <th class="p-3 text-left">File ID</th>
                        <th class="p-3 text-left">User</th>
                        <th class="p-3 text-left">Title</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Version</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("
                        SELECT f.*, u.username, fv.file_path 
                        FROM files f
                        JOIN users u ON f.user_id = u.id
                        JOIN file_versions fv ON f.id = fv.file_id AND f.current_version = fv.version
                        ORDER BY f.created_at DESC
                    ");
                    $stmt->execute();
                    $files = $stmt->get_result();
                    
                    while ($file = $files->fetch_assoc()):
                    ?>
                    <tr class="border-b">
                        <td class="p-3">#<?= htmlspecialchars($file['id']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($file['username']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($file['title']) ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                <?= ucfirst(htmlspecialchars($file['status'])) ?>
                            </span>
                        </td>
                        <td class="p-3">v<?= htmlspecialchars($file['current_version']) ?></td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <?php if(!empty($file['file_path'])): ?>
                                    <a href="uploads/<?= htmlspecialchars($file['file_path']) ?>" 
                                       class="text-blue-600 hover:text-blue-800"
                                       download>
                                        Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">No file</span>
                                <?php endif; ?>
                                
                                <form method="POST" enctype="multipart/form-data" 
                                
                                      class="flex items-center gap-2">
                                    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                    <input type="hidden" name="user_id" value="<?= $file['user_id'] ?>">
                                    <input type="file" name="processed_file" 
                                           class="text-sm" accept=".bin">
                                    <button type="submit" 
                                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                        Process
                                    </button>
                                </form>
                                
                                <form method="POST" 
                              
                                      class="ml-2"
                                      onsubmit="return confirm('Are you sure you want to permanently delete this file and all its versions? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                                    <button type="submit" 
                                            class="bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700">
                                        Delete
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
