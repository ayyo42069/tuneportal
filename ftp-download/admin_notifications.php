<?php
include 'config.php';
require_auth(true); // Ensure only admins can access

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    $action = $_POST['action'] ?? '';
    $message = sanitize($_POST['message'] ?? '');
    $recipient_id = ($_POST['recipient_id'] === 'all') ? null : intval($_POST['recipient_id']);

    if ($action === 'add' && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $recipient_id, $message);
        $stmt->execute();
    }
    
    if ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE notifications SET message=? WHERE id=?");
        $stmt->bind_param("si", $message, $id);
        $stmt->execute();
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Fetch existing notifications
$result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Fetch users for the dropdown
$users = $conn->query("SELECT id, username FROM users");

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-6">Manage Notifications</h2>
                
                <!-- Add Notification Form -->
                <form method="POST" class="space-y-4">
                    <?php echo csrf_input_field(); ?>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notification Message</label>
                        <textarea id="message" name="message" required placeholder="Enter notification message" class="w-full p-3 border rounded-lg focus:ring focus:ring-red-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label for="recipient_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recipient</label>
                        <select id="recipient_id" name="recipient_id" class="w-full p-3 border rounded-lg focus:ring focus:ring-red-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="all">All Users</option>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="action" value="add" class="w-full bg-red-600 text-white p-3 rounded-lg hover:bg-red-700 transition-colors duration-200">Send Notification</button>
                </form>

                <!-- Existing Notifications -->
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white mt-8 mb-4">Existing Notifications</h3>
                <div class="space-y-4">
                    <?php foreach ($notifications as $note): ?>
                        <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-700 shadow-md">
                            <p class="text-gray-700 dark:text-gray-300 mb-2"><?php echo htmlspecialchars($note['message']); ?></p>
                            <div class="flex justify-between items-center text-gray-500 dark:text-gray-400 text-sm">
                                <span><?php echo date('M j, Y H:i', strtotime($note['created_at'])); ?></span>
                                <div class="space-x-2">
                                    <button onclick="editNotification(<?php echo $note['id']; ?>, '<?php echo addslashes($note['message']); ?>')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>
                                    <form method="POST" class="inline">
                                        <?php echo csrf_input_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                        <button type="submit" name="action" value="delete" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Notification Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Edit Notification</h3>
        <form method="POST" id="editForm">
            <?php echo csrf_input_field(); ?>
            <input type="hidden" name="id" id="editId">
            <textarea name="message" id="editMessage" required class="w-full p-3 border rounded-lg focus:ring focus:ring-red-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white mb-4"></textarea>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" name="action" value="edit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function editNotification(id, message) {
    document.getElementById('editId').value = id;
    document.getElementById('editMessage').value = message;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php include 'footer.php'; ?>

