<?php
include 'config.php';
require_auth(true); // Admin-only

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    $user_id = (int)$_POST['user_id'];
    $amount = (int)$_POST['amount'];
    $description = htmlspecialchars(trim($_POST['description']));

    try {
        $conn->autocommit(FALSE);
        
        // Update user balance using a prepared statement
        $stmt = $conn->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
        $stmt->bind_param("ii", $amount, $user_id);
        $stmt->execute();
        
        // Record transaction
        $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'admin_adjust', ?)");
        $stmt->bind_param("iis", $user_id, $amount, $description);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success'] = "Credits updated successfully";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating credits: " . htmlspecialchars($e->getMessage());
    }
}

include 'header.php';
include 'includes/sidebar.php'; // Create similar to user sidebar
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-2xl font-bold text-red-600 mb-6">Manage User Credits</h2>
        
        <!-- User Search -->
        <div class="mb-6">
            <form method="GET" class="flex gap-4">
                <input type="text" name="search" placeholder="Search by email or user ID" 
                       class="flex-1 p-2 border rounded" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    Search
                </button>
            </form>
        </div>

        <!-- User List -->
        <?php
        $search = $_GET['search'] ?? '';
        $query = "SELECT id, username, email, credits FROM users";
        if (!empty($search)) {
            $query .= " WHERE email LIKE ? OR username LIKE ? OR id = ?";
            $stmt = $conn->prepare($query);
            $searchParam = "%$search%";
            $stmt->bind_param("ssi", $searchParam, $searchParam, (int)$search);
        } else {
            $stmt = $conn->prepare($query);
        }
        $stmt->execute();
        $users = $stmt->get_result();
        ?>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-red-50">
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Username</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Credits</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-3"><?= htmlspecialchars($user['id']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="p-3"><?= number_format($user['credits']) ?></td>
                        <td class="p-3">
                            <button onclick="showCreditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" 
                                    class="text-red-600 hover:text-red-800">
                                Adjust Credits
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Credit Adjustment Modal -->
<div id="creditModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Adjust Credits</h3>
            <form method="POST">
            <?php echo csrf_input_field(); ?>
                <input type="hidden" name="user_id" id="modalUserId">
                <div class="mb-4">
                    <label class="block mb-2">Username</label>
                    <input type="text" id="modalUsername" class="w-full p-2 border rounded" disabled>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Amount</label>
                    <input type="number" name="amount" required 
                           class="w-full p-2 border rounded" placeholder="Positive to add, negative to deduct">
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Description</label>
                    <textarea name="description" required 
                              class="w-full p-2 border rounded" rows="3"></textarea>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="hideCreditModal()" 
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreditModal(userId, username) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUsername').value = username;
    document.getElementById('creditModal').classList.remove('hidden');
}

function hideCreditModal() {
    document.getElementById('creditModal').classList.add('hidden');
}
</script>

<?php include 'footer.php'; ?>
