<?php
include 'config.php';
require_auth();


// Get current credits
$user = $conn->query("SELECT credits FROM users WHERE id = {$_SESSION['user_id']}")->fetch_assoc();
$_SESSION['credits'] = $user['credits'];

include 'header.php';
?>

<!-- Main Container -->
<div class="flex min-h-screen">
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 mt-16 ml-64 p-8"> <!-- mt-16 for header, ml-64 for sidebar -->
        <!-- Credit Balance Card -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold">Credit Balance</h3>
                <p class="text-3xl font-bold text-red-600">
                    <?= number_format($_SESSION['credits']) ?> Credits
                </p>
            </div>
            <a href="credits.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Transaction History
            </a>
        </div>
    </div>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-red-600 mb-4">Welcome Back, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
            <!-- Notifications -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-semibold mb-4">Notifications</h3>
    <?php
    $notifications = $conn->query("
        SELECT * FROM notifications 
        WHERE user_id = {$_SESSION['user_id']} 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    
    if ($notifications->num_rows > 0):
    ?>
    <div class="space-y-2">
        <?php while ($note = $notifications->fetch_assoc()): ?>
        <div class="p-3 border rounded <?= $note['is_read'] ? 'bg-gray-50' : 'bg-blue-50' ?>">
            <p class="text-sm">
                <?= $note['message'] ?>
                <?php if ($note['link']): ?>
                <a href="<?= $note['link'] ?>" class="text-red-600 hover:text-red-800 ml-2">
                    View →</a>
                <?php endif; ?>
            </p>
            <p class="text-xs text-gray-500 mt-1">
                <?= date('M j, Y H:i', strtotime($note['created_at'])) ?>
            </p>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p class="text-gray-500">No new notifications</p>
    <?php endif; ?>
</div>
            <!-- File Upload Card -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">Upload New File</h3>
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">File Title</label>
                            <input type="text" name="title" required class="w-full p-2 border rounded">
                        </div>
                        <div>
                            <label class="block mb-2">Car Model</label>
                            <input type="text" name="car_model" required class="w-full p-2 border rounded">
                        </div>
                        <div class="col-span-2">
                            <label class="block mb-2">Description</label>
                            <textarea name="description" class="w-full p-2 border rounded" rows="3"></textarea>
                        </div>
                        <div>
                            <label class="block mb-2">Tuning Options</label>
                            <?php
                            $options = $conn->query("SELECT * FROM tuning_options");
                            while ($opt = $options->fetch_assoc()) :
                            ?>
                                <label class="block mb-2">
                                    <input type="checkbox" name="tuning_options[]" value="<?= $opt['id'] ?>">
                                    <?= $opt['name'] ?> (<?= $opt['credit_cost'] ?> credits)
                                </label>
                            <?php endwhile; ?>
                        </div>
                        <div>
                            <label class="block mb-2">Select .bin File</label>
                            <input type="file" name="bin_file" accept=".bin" required class="w-full p-2 border rounded bg-white">
                        </div>
                    </div>
                    <button type="submit" class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Upload File
                    </button>
                </form>
            </div>

            <!-- File List -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Files</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-red-50">
                                <th class="p-2 text-left">Title</th>
                                <th class="p-2 text-left">Car Model</th>
                                <th class="p-2 text-left">Status</th>
                                <th class="p-2 text-left">Version</th>
                                <th class="p-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $userId = $_SESSION['user_id'];
                            $files = $conn->query("SELECT * FROM files WHERE user_id = $userId ORDER BY created_at DESC LIMIT 5");
                            while ($file = $files->fetch_assoc()) :
                            ?>
                                <tr class="border-b">
                                    <td class="p-2"><?= $file['title'] ?></td>
                                    <td class="p-2"><?= $file['car_model'] ?></td>
                                    <td class="p-2">
                                        <span class="px-2 py-1 rounded <?= $file['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= ucfirst($file['status']) ?>
                                        </span>
                                    </td>
                                    <td class="p-2">v<?= $file['current_version'] ?></td>
                                    <td class="p-2">
                                        <a href="file_details.php?id=<?= $file['id'] ?>" class="text-red-600 hover:text-red-800">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>