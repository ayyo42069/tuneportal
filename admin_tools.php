<?php
include 'config.php';
require_auth(true);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete tool
    if (isset($_POST['delete'])) {
        $tool_id = (int)$_POST['tool_id'];
        $tool = $conn->query("SELECT file_path FROM tools WHERE id = $tool_id")->fetch_assoc();
        
        // Delete file if exists
        if ($tool['file_path'] && file_exists($tool['file_path'])) {
            unlink($tool['file_path']);
        }
        
        // Delete from database
        $conn->query("DELETE FROM tools WHERE id = $tool_id");
        $_SESSION['success'] = "Tool deleted successfully";
    }
    // Add/Edit tool
    else {
        $tool_id = isset($_POST['tool_id']) ? (int)$_POST['tool_id'] : null;
        $name = sanitize($_POST['name']);
        $category_id = (int)$_POST['category_id'];
        $description = sanitize($_POST['description']);
        $download_url = filter_var($_POST['download_url'], FILTER_SANITIZE_URL);

        // File upload handling
        $file_path = null;
        if (!empty($_FILES['tool_file']['name'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/tools/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            
            // Create upload directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $_SESSION['error'] = "Failed to create upload directory";
                    header("Location: admin_tools.php");
                    exit;
                }
            }
            
            // Get file extension
            $ext = strtolower(pathinfo($_FILES['tool_file']['name'], PATHINFO_EXTENSION));
            
            // Validate file type
            $allowed_extensions = ['zip', 'exe', 'rar', '7z', 'bin'];
            if (!in_array($ext, $allowed_extensions)) {
                $_SESSION['error'] = "Invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
                header("Location: admin_tools.php");
                exit;
            }

            // Validate file size (10MB limit)
            $max_size = 10 * 1024 * 1024; // 10MB
            if ($_FILES['tool_file']['size'] > $max_size) {
                $_SESSION['error'] = "File too large. Maximum size: 10MB";
                header("Location: admin_tools.php");
                exit;
            }

            // Generate unique filename
            $filename = uniqid() . '.' . $ext;
            $full_path = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['tool_file']['tmp_name'], $full_path)) {
                $file_path = 'tools/uploads/' . $filename;
                
                // Delete old file if updating
                if ($tool_id) {
                    $old_tool = $conn->query("SELECT file_path FROM tools WHERE id = $tool_id")->fetch_assoc();
                    if ($old_tool['file_path'] && file_exists($old_tool['file_path'])) {
                        unlink($old_tool['file_path']);
                    }
                }
            } else {
                $_SESSION['error'] = "Failed to upload file. Please check permissions.";
                header("Location: admin_tools.php");
                exit;
            }
        }

        // Database operations
        try {
            if ($tool_id) {
                // Update existing tool
                $conn->query("UPDATE tools SET 
                    category_id = $category_id,
                    name = '$name',
                    description = '$description',
                    download_url = '$download_url',
                    file_path = " . ($file_path ? "'$file_path'" : "file_path") . "
                    WHERE id = $tool_id
                ");
                $_SESSION['success'] = "Tool updated successfully";
            } else {
                // Insert new tool
                $conn->query("INSERT INTO tools 
                    (category_id, name, description, download_url, file_path)
                    VALUES ($category_id, '$name', '$description', '$download_url', '$file_path')
                ");
                $_SESSION['success'] = "Tool added successfully";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    }
    header("Location: admin_tools.php");
    exit;
}

// Get data for display
$categories = $conn->query("SELECT * FROM tool_categories ORDER BY name");
$tools = $conn->query("
    SELECT t.*, tc.name AS category_name 
    FROM tools t
    JOIN tool_categories tc ON t.category_id = tc.id
    ORDER BY tc.name, t.name
");

include 'header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 mt-16 ml-64 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-red-600 mb-6">Manage Tools</h2>
        
        <!-- Add/Edit Form -->
        <form method="POST" enctype="multipart/form-data" class="mb-8 p-4 bg-gray-50 rounded-lg">
            <input type="hidden" name="tool_id" id="tool_id" value="">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2">Category</label>
                    <select name="category_id" required class="w-full p-2 border rounded">
                        <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block mb-2">Tool Name</label>
                    <input type="text" name="name" required class="w-full p-2 border rounded">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2">Description</label>
                    <textarea name="description" class="w-full p-2 border rounded" rows="3"></textarea>
                </div>
                <div>
                    <label class="block mb-2">Upload File (ZIP, EXE, RAR, 7Z, BIN)</label>
                    <input type="file" name="tool_file" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block mb-2">OR Download URL</label>
                    <input type="url" name="download_url" class="w-full p-2 border rounded">
                </div>
            </div>
            <button type="submit" class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Save Tool
            </button>
        </form>

        <!-- Tools List -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-red-50">
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Category</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($tool = $tools->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-3"><?= htmlspecialchars($tool['name']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($tool['category_name']) ?></td>
                        <td class="p-3">
                            <?= $tool['file_path'] ? 'File' : 'URL' ?>
                        </td>
                        <td class="p-3">
                            <button onclick="editTool(<?= $tool['id'] ?>)" 
                                    class="text-blue-600 hover:text-blue-800 mr-2">
                                Edit
                            </button>
                            <form method="POST" class="inline">
                                <input type="hidden" name="tool_id" value="<?= $tool['id'] ?>">
                                <button type="submit" name="delete" 
                                        class="text-red-600 hover:text-red-800"
                                        onclick="return confirm('Are you sure you want to delete this tool?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<script>
function editTool(toolId) {
    fetch(`get_tool.php?id=${toolId}`)
        .then(response => response.json())
        .then(tool => {
            document.getElementById('tool_id').value = tool.id;
            document.querySelector('[name="category_id"]').value = tool.category_id;
            document.querySelector('[name="name"]').value = tool.name;
            document.querySelector('[name="description"]').value = tool.description;
            document.querySelector('[name="download_url"]').value = tool.download_url;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
}
</script>

<?php include 'footer.php'; ?>