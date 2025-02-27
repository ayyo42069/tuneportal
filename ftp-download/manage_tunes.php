<?php
include 'config.php';
require_auth(true); // Admin only

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token before proceeding
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($token)) {
        die("Error: Invalid CSRF token.");
    }

    // Add new tuning option
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $credit_cost = (int)$_POST['credit_cost'];
        
        $stmt = $conn->prepare("INSERT INTO tuning_options (name, description, credit_cost) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $credit_cost);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Tuning option added successfully";
        } else {
            $_SESSION['error'] = "Error adding tuning option";
        }
    }
    
    // Update existing tuning option
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $description = $conn->real_escape_string($_POST['description']);
        $credit_cost = (int)$_POST['credit_cost'];
        
        $stmt = $conn->prepare("UPDATE tuning_options SET name = ?, description = ?, credit_cost = ? WHERE id = ?");
        $stmt->bind_param("ssii", $name, $description, $credit_cost, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Tuning option updated successfully";
        } else {
            $_SESSION['error'] = "Error updating tuning option";
        }
    }
    
    // Delete tuning option
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        
        if ($conn->query("DELETE FROM tuning_options WHERE id = $id")) {
            $_SESSION['success'] = "Tuning option deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting tuning option";
        }
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-100 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-red-600 dark:text-red-400">Manage Tuning Options</h2>
                    <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                        Add New Option
                    </button>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['success'] ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-red-50 dark:bg-red-900">
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">ID</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Name</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Description</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Credit Cost</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Created At</th>
                                <th class="p-3 text-left text-gray-800 dark:text-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $options = $conn->query("SELECT * FROM tuning_options ORDER BY created_at DESC");
                            while ($option = $options->fetch_assoc()):
                            ?>
                            <tr class="border-b dark:border-gray-700">
                                <td class="p-3 text-gray-700 dark:text-gray-300">#<?= $option['id'] ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($option['name']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= htmlspecialchars($option['description']) ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= $option['credit_cost'] ?></td>
                                <td class="p-3 text-gray-700 dark:text-gray-300"><?= date('Y-m-d H:i', strtotime($option['created_at'])) ?></td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        <button onclick="editOption(<?= htmlspecialchars(json_encode($option)) ?>)"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            Edit
                                        </button>
                                        
                                        <form method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this tuning option?');">
                                              <?php echo csrf_input_field(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $option['id'] ?>">
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
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
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Add New Tuning Option</h3>
        <form method="POST">
        <?php echo csrf_input_field(); ?>
            <input type="hidden" name="action" value="add">
            
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Name</label>
                <input type="text" name="name" required
                       class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Description</label>
                <textarea name="description" required
                          class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Credit Cost</label>
                <input type="number" name="credit_cost" required min="1"
                       class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button"
                        onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                    Add Option
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-200">Edit Tuning Option</h3>
        <form method="POST">
        <?php echo csrf_input_field(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Name</label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Description</label>
                <textarea name="description" id="edit_description" required
                          class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Credit Cost</label>
                <input type="number" name="credit_cost" id="edit_credit_cost" required min="1"
                       class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button"
                        onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                    Update Option
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editOption(option) {
    document.getElementById('edit_id').value = option.id;
    document.getElementById('edit_name').value = option.name;
    document.getElementById('edit_description').value = option.description;
    document.getElementById('edit_credit_cost').value = option.credit_cost;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>

<?php include 'footer.php'; ?>

