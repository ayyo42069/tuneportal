<?php
include 'config.php';
require_auth(true); // Ensure only admins can access
include 'header.php';
include 'sidebar.php';

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $action = $_POST['action'] ?? '';
    $message = sanitize($_POST['message'] ?? '');
    $recipient_id = $_POST['recipient_id'] ?? 'all';
    
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

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<div class="p-6 bg-gray-100 min-h-screen">
    <h2 class="text-2xl font-semibold mb-4">Manage Notifications</h2>
    <form method="POST" class="bg-white p-4 rounded-lg shadow-md">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <textarea name="message" required placeholder="Notification message" class="w-full p-2 border rounded-md"></textarea>
        <select name="recipient_id" class="w-full p-2 border rounded-md mt-2">
            <option value="all">All Users</option>
            <?php
            $users = $conn->query("SELECT id, username FROM users");
            while ($user = $users->fetch_assoc()) {
                echo "<option value='{$user['id']}'>{$user['username']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="action" value="add" class="mt-3 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Send Notification</button>
    </form>

    <h3 class="text-xl font-semibold mt-6">Existing Notifications</h3>
    <ul class="mt-4">
        <?php foreach ($notifications as $note): ?>
            <li class="bg-white p-4 rounded-lg shadow-md mb-2 flex justify-between items-center">
                <span><?php echo htmlspecialchars($note['message']); ?></span>
                <form method="POST" class="flex items-center space-x-2">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                    <textarea name="message" class="p-2 border rounded-md"><?php echo htmlspecialchars($note['message']); ?></textarea>
                    <button type="submit" name="action" value="edit" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600">Edit</button>
                    <button type="submit" name="action" value="delete" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php include 'footer.php'; ?>
