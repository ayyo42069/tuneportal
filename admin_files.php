<?php
include 'config.php';
require_auth(true); // Admin only

// Handle file processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_id = (int)$_POST['file_id'];
    $user_id = (int)$_POST['user_id'];
    
    // Upload processed file
    if (!empty($_FILES['processed_file']['name'])) {
        $file = $_FILES['processed_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if ($ext === 'bin') {
            // Get current version
            $current = $conn->query("SELECT current_version FROM files WHERE id = $file_id")->fetch_assoc();
            $new_version = $current['current_version'] + 1;
            
            // Store file
            $uploadDir = __DIR__ . '/uploads/';
            $filename = "processed_{$file_id}_v{$new_version}.bin";
            move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
            
            // Update database
            $conn->query("UPDATE files SET status = 'processed', current_version = $new_version WHERE id = $file_id");
            $conn->query("INSERT INTO file_versions (file_id, version, file_path) VALUES ($file_id, $new_version, '$filename')");
            
            // Create notification
            $message = "Your file #$file_id has been processed!";
            $link = "file_details.php?id=$file_id";
            $conn->query("INSERT INTO notifications (user_id, message, link) VALUES ($user_id, '$message', '$link')");
            
            $_SESSION['success'] = "File processed successfully";
        } else {
            $_SESSION['error'] = "Only .bin files accepted";
        }
    }
}

include 'header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-red-600 mb-6">Manage User Files</h2>
        
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
                    $files = $conn->query("
                    SELECT f.*, u.username, fv.file_path 
                    FROM files f
                    JOIN users u ON f.user_id = u.id
                    JOIN file_versions fv ON f.id = fv.file_id AND f.current_version = fv.version
                    ORDER BY f.created_at DESC
                ");
                    
                    while ($file = $files->fetch_assoc()):
                    ?>
                    <tr class="border-b">
                        <td class="p-3">#<?= $file['id'] ?></td>
                        <td class="p-3"><?= $file['username'] ?></td>
                        <td class="p-3"><?= $file['title'] ?></td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                <?= ucfirst($file['status']) ?>
                            </span>
                        </td>
                        <td class="p-3">v<?= $file['current_version'] ?></td>
                        <td class="p-3">
    <div class="flex items-center gap-2">
        <?php if(!empty($file['file_path'])): ?>
            <a href="uploads/<?= $file['file_path'] ?>" 
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