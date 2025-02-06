<?php
include 'config.php';
require_auth();

$userId = $_SESSION['user_id'];

// Use a prepared statement to fetch user files
$stmt = $conn->prepare("
    SELECT f.*, fv.uploaded_at, fv.file_path 
    FROM files f
    LEFT JOIN file_versions fv 
        ON f.id = fv.file_id AND f.current_version = fv.version
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$files = $stmt->get_result();

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-red-600 dark:text-red-400">My Files</h2>
                    <a href="dashboard.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                        Upload New File
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-red-50 dark:bg-red-900">
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Title</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Car Model</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Status</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Current Version</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Last Updated</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($files->num_rows > 0): ?>
                                <?php while ($file = $files->fetch_assoc()): ?>
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($file['title']) ?></td>
                                        <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($file['car_model']) ?></td>
                                        <td class="p-3">
                                            <span class="px-2 py-1 rounded <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' ?>">
                                                <?= ucfirst(htmlspecialchars($file['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-gray-700 dark:text-gray-300">v<?= htmlspecialchars($file['current_version']) ?></td>
                                        <td class="p-3 text-gray-700 dark:text-gray-300"><?= $file['uploaded_at'] ? date('M j, Y H:i', strtotime($file['uploaded_at'])) : 'N/A' ?></td>
                                        <td class="p-3">
                                            <div class="flex items-center gap-2">
                                                <a href="file_details.php?id=<?= htmlspecialchars($file['id']) ?>" 
                                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                    View
                                                </a>
                                                <?php if ($file['status'] === 'processed' && !empty($file['file_path'])): ?>
                                                    <a href="uploads/<?= htmlspecialchars($file['file_path']) ?>" 
                                                       class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
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
                                    <td colspan="6" class="p-3 text-center text-gray-500 dark:text-gray-400">
                                        No files found. <a href="dashboard.php" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Upload your first file</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

