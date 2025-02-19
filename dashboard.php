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

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-50 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <!-- Credit Balance Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white"><?= __('credit_balance', 'dashboard') ?></h3>
                        <p class="text-3xl font-bold text-red-600">
                            <?= number_format($_SESSION['credits']) ?> <?= __('credits', 'dashboard') ?>
                        </p>
                    </div>
                    <a href="credits.php" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <?= __('transaction_history', 'dashboard') ?>
                    </a>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6"><?= __('welcome_back', 'dashboard') ?>, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
                
                <!-- Grid Layout for Cards -->
                <div class="grid gap-8 grid-cols-1 lg:grid-cols-2">
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
                            
                            <div class="grid gap-4 sm:grid-cols-2">
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
                            </div>
                            <div class="grid gap-4 sm:grid-cols-3">
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
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('car_model', 'dashboard') ?></label>
                                <input type="text" name="car_model" required 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= __('description', 'dashboard') ?></label>
                            <textarea name="description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
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
                                    <?php
                                    endwhile;
                                    ?>
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
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Upload File
                            </button>
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
        .then(response => response.json())
        .then(data => {
            modelSelect.innerHTML = '<option value=""><?= __('select_model', 'dashboard') ?></option>';
            data.forEach(model => {
                modelSelect.innerHTML += `<option value="${model.id}" data-start="${model.year_start}" data-end="${model.year_end}">${model.name}</option>`;
            });
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
