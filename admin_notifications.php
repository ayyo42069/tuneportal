<?php
include 'config.php';
require_auth(true); // Ensure only admins can access
include 'header.php';
include 'sidebar.php';

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        die(json_encode(['error' => 'Invalid CSRF token.']));
    }

    $action = $_POST['action'] ?? '';
    $message = sanitize($_POST['message'] ?? '');
    $recipient_id = ($_POST['recipient_id'] === 'all') ? null : $_POST['recipient_id'];

    
    if ($action === 'add' && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param($recipient_id === 'all' ? "is" : "iis", $recipient_id, $message);
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

?>

<div class="p-6 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Manage Notifications</h2>
        <form method="POST" class="space-y-4">
        <?php echo csrf_input_field(); ?>
            <textarea name="message" required placeholder="Notification message" class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300"></textarea>
            <select name="recipient_id" class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300">
                <option value="all">All Users</option>
                <?php
                $users = $conn->query("SELECT id, username FROM users");
                while ($user = $users->fetch_assoc()) {
                    echo "<option value='{$user['id']}'>{$user['username']}</option>";
                }
                ?>
            </select>
            <button type="submit" name="action" value="add" class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700">Send Notification</button>
        </form>

        <h3 class="text-xl font-semibold text-gray-800 mt-8">Existing Notifications</h3>
        <div class="mt-4 space-y-4">
            <?php foreach ($notifications as $note): ?>
                <div class="p-4 border rounded-lg bg-gray-50 shadow-md">
                    <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($note['message']); ?></p>
                    <div class="flex justify-between items-center text-gray-500 text-sm">
                        <span><?php echo date('M j, Y H:i', strtotime($note['created_at'])); ?></span>
                        <div class="space-x-2">
                            <form method="POST" class="inline">
                            <?php echo csrf_input_field(); ?>
                               <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                <textarea name="message" class="hidden"><?php echo htmlspecialchars($note['message']); ?></textarea>
                                <button type="submit" name="action" value="edit" class="text-blue-600 hover:text-blue-800">Edit</button>
                            </form>
                            <form method="POST" class="inline">
                            <?php echo csrf_input_field(); ?>
                              <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                <button type="submit" name="action" value="delete" class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
