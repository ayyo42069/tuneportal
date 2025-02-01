<?php
include 'config.php';
require_auth();

include 'header.php';
include 'includes/sidebar.php';

$userId = $_SESSION['user_id'];
$files = $conn->query("
    SELECT f.*, fv.uploaded_at, fv.file_path 
    FROM files f
    LEFT JOIN file_versions fv 
        ON f.id = fv.file_id AND f.current_version = fv.version
    WHERE f.user_id = $userId
    ORDER BY f.created_at DESC
");
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-red-600">My Files</h2>
            <a href="dashboard.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Upload New File
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-red-50">
                        <th class="p-3 text-left">Title</th>
                        <th class="p-3 text-left">Car Model</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Current Version</th>
                        <th class="p-3 text-left">Last Updated</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($files->num_rows > 0): ?>
                        <?php while ($file = $files->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="p-3"><?= htmlspecialchars($file['title']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($file['car_model']) ?></td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                        <?= ucfirst($file['status']) ?>
                                    </span>
                                </td>
                                <td class="p-3">v<?= $file['current_version'] ?></td>
                                <td class="p-3"><?= $file['uploaded_at'] ? date('M j, Y H:i', strtotime($file['uploaded_at'])) : 'N/A' ?></td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <a href="file_details.php?id=<?= $file['id'] ?>" 
                                           class="text-blue-600 hover:text-blue-800">
                                            View
                                        </a>
                                        <?php if($file['status'] === 'processed' && !empty($file['file_path'])): ?>
                                            <a href="uploads/<?= $file['file_path'] ?>" 
                                               class="text-green-600 hover:text-green-800"
                                               download>
                                                Download
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-3 text-center text-gray-500">
                                No files found. <a href="dashboard.php" class="text-red-600 hover:text-red-800">Upload your first file</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>