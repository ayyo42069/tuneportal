<?php
include 'config.php';
require_auth();

// Get current credits using a prepared statement
$stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$_SESSION['credits'] = $user['credits'];
$stmt->close();

// Get quick statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_files,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_files,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_files
    FROM files 
    WHERE user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get recent files
$stmt = $conn->prepare("
    SELECT f.*, m.name as manufacturer_name, cm.name as model_name, f.status,
           COALESCE(f.updated_at, f.created_at) as last_modified
    FROM files f
    JOIN car_manufacturers m ON f.manufacturer_id = m.id
    JOIN car_models cm ON f.model_id = cm.id
    WHERE f.user_id = ?
    ORDER BY last_modified DESC
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_files = $stmt->get_result();
$stmt->close();
include 'header.php';
?>

<div class="flex min-h-screen bg-gray-50 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">
                <?= __('welcome_back', 'dashboard') ?>, <?= htmlspecialchars($_SESSION['username']) ?>!
            </h2>

            <!-- Quick Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Credit Balance Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white"><?= __('credit_balance', 'dashboard') ?></h3>
                            <p class="text-3xl font-bold text-red-600">
                                <?= number_format($_SESSION['credits']) ?>
                            </p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <a href="credits.php" class="mt-4 text-sm text-red-600 hover:text-red-700 inline-flex items-center">
                        <?= __('transaction_history', 'dashboard') ?> →
                    </a>
                </div>

                <!-- Total Files Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Total Files</h3>
                            <p class="text-3xl font-bold text-blue-600"><?= $stats['total_files'] ?></p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <a href="files.php" class="mt-4 text-sm text-blue-600 hover:text-blue-700 inline-flex items-center">
                        View all files →
                    </a>
                </div>

                <!-- Pending Files Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Pending Files</h3>
                            <p class="text-3xl font-bold text-yellow-600"><?= $stats['pending_files'] ?></p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Approved Files Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Approved Files</h3>
                            <p class="text-3xl font-bold text-green-600"><?= $stats['approved_files'] ?></p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-8 grid-cols-1 lg:grid-cols-2">
                <!-- Recent Files Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Files</h3>
                        <a href="files.php" class="text-sm text-red-600 hover:text-red-700">View all →</a>
                    </div>
                    <?php if ($recent_files->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($file = $recent_files->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($file['title']) ?></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <?= htmlspecialchars($file['manufacturer_name']) ?> <?= htmlspecialchars($file['model_name']) ?>
                                    </p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?= $file['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= ucfirst($file['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <a href="file_details.php?id=<?= $file['id'] ?>" class="text-sm text-red-600 hover:text-red-700">
                                Details →
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No files uploaded yet</p>
                    <?php endif; ?>
                </div>

                <!-- Notifications Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white"><?= __('notifications', 'dashboard') ?></h3>
                        <span class="bg-red-100 text-red-600 text-sm py-1 px-3 rounded-full"><?= __('new', 'dashboard') ?></span>
                    </div>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC LIMIT 5");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $notifications = $stmt->get_result();
                    
                    if ($notifications->num_rows > 0):
                    ?>
                    <div class="space-y-3">
                        <?php while ($note = $notifications->fetch_assoc()): ?>
                        <div class="p-4 border rounded-lg <?= $note['is_read'] ? 'bg-gray-50 dark:bg-gray-700' : 'bg-blue-50 dark:bg-blue-900 border-blue-100 dark:border-blue-800' ?>">
                            <div class="flex items-start justify-between">
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    <?= htmlspecialchars($note['message']) ?>
                                </p>
                                <?php if (!$note['is_read']): ?>
                                    <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full ml-2 mt-2"></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= date('M j, Y H:i', strtotime($note['created_at'])) ?>
                                </span>
                                <?php if ($note['link']): ?>
                                <a href="<?= htmlspecialchars($note['link']) ?>" 
                                   class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">
                                    <?= __('view_details', 'dashboard') ?> →
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4"><?= __('no_notifications', 'dashboard') ?></p>
                    <?php endif; ?>
                    <?php $stmt->close(); ?>
                </div>
<!-- File Upload Card -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><?= __('upload_new_file', 'dashboard') ?></h3>
    <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <?php echo csrf_input_field(); ?>
        
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Left Column -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('file_title', 'dashboard') ?></label>
                    <input type="text" name="title" required 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('manufacturer', 'dashboard') ?></label>
                    <select name="manufacturer" id="manufacturer" required onchange="loadModels()"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                        <option value=""><?= __('select_manufacturer', 'dashboard') ?></option>
                        <?php
                        $manufacturers = $conn->query("SELECT * FROM car_manufacturers ORDER BY name");
                        while ($manufacturer = $manufacturers->fetch_assoc()) {
                            echo "<option value='" . $manufacturer['id'] . "'>" . htmlspecialchars($manufacturer['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="grid gap-4 grid-cols-3">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('model', 'dashboard') ?></label>
                        <select name="model" id="model" required onchange="loadECUs()" disabled
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                            <option value=""><?= __('select_model', 'dashboard') ?></option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('year', 'dashboard') ?></label>
                        <select name="year" id="year" required disabled
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                            <option value=""><?= __('select_year', 'dashboard') ?></option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('ecu_type', 'dashboard') ?></label>
                        <select name="ecu_type" id="ecu_type" required disabled
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                            <option value=""><?= __('select_ecu', 'dashboard') ?></option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('description', 'dashboard') ?></label>
                    <textarea name="description" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('tuning_options', 'dashboard') ?></label>
                    <div class="space-y-2 max-h-40 overflow-y-auto p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <?php
                        $options = $conn->query("SELECT * FROM tuning_options");
                        while ($opt = $options->fetch_assoc()) :
                        ?>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="tuning_options[]" value="<?= $opt['id'] ?>"
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500 dark:border-gray-600 dark:bg-gray-700">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <?= htmlspecialchars($opt['name']) ?> 
                                <span class="text-gray-500 dark:text-gray-400">(<?= $opt['credit_cost'] ?> <?= __('credits_cost', 'dashboard') ?>)</span>
                            </span>
                        </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('select_file', 'dashboard') ?></label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-red-500 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-red-600 dark:text-red-400 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                    <span>Upload a file</span>
                                    <input type="file" name="bin_file" accept=".bin" class="sr-only" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">.bin files only</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload File
                    </button>
                </div>
            </div>
        </div>
    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this JavaScript at the bottom of the file, before the footer include -->
<script>
function loadModels() {
    const manufacturerId = document.getElementById('manufacturer').value;
    const modelSelect = document.getElementById('model');
    const yearSelect = document.getElementById('year');
    const ecuSelect = document.getElementById('ecu_type');
    
    modelSelect.disabled = !manufacturerId;
    yearSelect.disabled = true;
    ecuSelect.disabled = true;
    
    if (!manufacturerId) return;

    fetch(`get_models.php?manufacturer_id=${manufacturerId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            modelSelect.innerHTML = '<option value=""><?= __('select_model', 'dashboard') ?></option>';
            data.forEach(model => {
                modelSelect.innerHTML += `<option value="${model.id}" data-start="${model.year_start}" data-end="${model.year_end}">${model.name}</option>`;
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load models. Please try again.');
        });
}

function loadYears() {
    const modelSelect = document.getElementById('model');
    const yearSelect = document.getElementById('year');
    const selectedOption = modelSelect.options[modelSelect.selectedIndex];
    
    const startYear = parseInt(selectedOption.dataset.start);
    const endYear = parseInt(selectedOption.dataset.end);
    
    yearSelect.innerHTML = '<option value=""><?= __('select_year', 'dashboard') ?></option>';
    for (let year = endYear; year >= startYear; year--) {
        yearSelect.innerHTML += `<option value="${year}">${year}</option>`;
    }
    yearSelect.disabled = false;
}

function loadECUs() {
    const modelId = document.getElementById('model').value;
    const ecuSelect = document.getElementById('ecu_type');
    
    if (!modelId) return;
    
    loadYears();

    fetch(`get_ecus.php?model_id=${modelId}`)
        .then(response => response.json())
        .then(data => {
            ecuSelect.innerHTML = '<option value=""><?= __('select_ecu', 'dashboard') ?></option>';
            data.forEach(ecu => {
                ecuSelect.innerHTML += `<option value="${ecu.id}">${ecu.name}</option>`;
            });
            ecuSelect.disabled = false;
        });
}
</script>

<?php include 'footer.php'; ?>
