<?php
include 'config.php';
require_auth();

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'])) {
    $action = $_POST['action'];

    switch ($action) {
        // In the update_preferences case
        case 'update_preferences':
            $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
            $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $language = sanitize($_POST['language']);
        
            $stmt = $conn->prepare("UPDATE user_preferences SET dark_mode = ?, email_notifications = ?, language = ? WHERE user_id = ?");
            $stmt->bind_param("iisi", $dark_mode, $email_notifications, $language, $user_id);
            $_SESSION['language'] = $language; // Add this line
            $_SESSION['success'] = __('preferences_updated', 'settings');
            $stmt->execute();
            break;

        case 'change_email':
            $new_email = sanitize($_POST['new_email']);
            $reason = sanitize($_POST['reason']);
            $password = $_POST['current_password'];

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $_SESSION['error'] = "Invalid password.";
                break;
            }

            // Check if email is different
            if ($new_email === $user['email']) {
                $_SESSION['error'] = "New email must be different from current email.";
                break;
            }

            // Create email change request
            $stmt = $conn->prepare("INSERT INTO email_change_requests (user_id, new_email, reason, status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("iss", $user_id, $new_email, $reason);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Email change request submitted for admin approval.";
                log_error("User submitted email change request", "INFO", [
                    'user_id' => $user_id,
                    'new_email' => $new_email
                ]);
            }
            break;

        case 'change_password':
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (!password_verify($current_password, $user['password'])) {
                $_SESSION['error'] = "Current password is incorrect.";
                break;
            }

            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = "New passwords do not match.";
                break;
            }

            if (strlen($new_password) < 8) {
                $_SESSION['error'] = "Password must be at least 8 characters long.";
                break;
            }

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Password updated successfully.";
                log_error("User changed password", "INFO", ['user_id' => $user_id]);
            }
            break;

        case 'update_profile':
            $timezone = sanitize($_POST['timezone']);
            $company = sanitize($_POST['company']);
            $phone = sanitize($_POST['phone']);

            $stmt = $conn->prepare("UPDATE user_profiles SET timezone = ?, company = ?, phone = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $timezone, $company, $phone, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Profile updated successfully.";
                log_error("User updated profile", "INFO", [
                    'user_id' => $user_id,
                    'changes' => [
                        'timezone' => $timezone,
                        'company' => $company
                    ]
                ]);
            }
            break;
    }
    
    header("Location: settings.php");
    exit();
}
// Check if user_preferences table exists and create if needed
$table_check = $conn->query("SHOW TABLES LIKE 'user_preferences'");
if ($table_check->num_rows == 0) {
    $conn->query("
        CREATE TABLE user_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            dark_mode TINYINT(1) DEFAULT 0,
            email_notifications TINYINT(1) DEFAULT 1,
            language VARCHAR(5) DEFAULT 'en',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            UNIQUE KEY unique_user (user_id)
        )
    ");
}

// After fetching preferences
$stmt = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$preferences = $stmt->get_result()->fetch_assoc();

// Add this code to create default preferences if none exist
if (!$preferences) {
    $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, dark_mode, email_notifications, language) VALUES (?, 0, 1, 'en')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $preferences = [
        'dark_mode' => 0,
        'email_notifications' => 1,
        'language' => 'en'
    ];
}
// Single, clean profile handling section
try {
    // First attempt to fetch existing profile
    $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();

    // If no profile exists, create one
    if (!$profile) {
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, timezone, company, phone) VALUES (?, 'UTC', '', '')");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Fetch the newly created profile
        $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc();
    }
} catch (mysqli_sql_exception $e) {
    // Handle potential race condition
    if ($e->getCode() === 1062) { // Duplicate key error
        $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc();
    } else {
        throw $e; // Re-throw other errors
    }
}
// Remove all other profile fetching/creation code and continue with the rest of the file
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Fetch pending email change request
$stmt = $conn->prepare("SELECT * FROM email_change_requests WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_email_request = $stmt->get_result()->fetch_assoc();

// Check if login_attempts table exists and create if needed
$table_check = $conn->query("SHOW TABLES LIKE 'login_attempts'");
if ($table_check->num_rows == 0) {
    $conn->query("
        CREATE TABLE login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            success TINYINT(1) DEFAULT 0,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
}

// Initialize login history as empty array if no records exist
$login_history = [];

// Fetch login history
$stmt = $conn->prepare("
    SELECT * FROM login_attempts 
    WHERE user_id = ? 
    ORDER BY attempted_at DESC 
    LIMIT 10
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$login_history = $stmt->get_result();

include 'header.php';
?>

<div class="flex min-h-screen bg-gray-50 dark:bg-gray-900">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 transition-all duration-300 lg:ml-64">
        <div class="container mx-auto px-4 py-8 mt-16">
            <div class="grid gap-8 grid-cols-1 lg:grid-cols-2">
                <!-- Preferences -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><?= __('preferences', 'settings') ?></h3>
                    <form method="POST" class="space-y-4">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="action" value="update_preferences">
                        
                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-700 dark:text-gray-300"><?= __('dark_mode', 'settings') ?></label>
                            <label class="switch">
                                <input type="checkbox" name="dark_mode" <?= $preferences['dark_mode'] ? 'checked' : '' ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-700 dark:text-gray-300"><?= __('email_notifications', 'settings') ?></label>
                            <label class="switch">
                                <input type="checkbox" name="email_notifications" <?= $preferences['email_notifications'] ? 'checked' : '' ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-300"><?= __('language', 'settings') ?></label>
                            <select name="language" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                                <option value="en" <?= $preferences['language'] === 'en' ? 'selected' : '' ?>>English</option>
                                <option value="de" <?= $preferences['language'] === 'de' ? 'selected' : '' ?>>Deutsch</option>
                                <option value="hu" <?= $preferences['language'] === 'hu' ? 'selected' : '' ?>>Magyar</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <?= __('save_preferences', 'settings') ?>
                        </button>
                    </form>
                </div>
                
                <!-- Email Change -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><?= __('change_email', 'settings') ?></h3>
                    <?php if ($pending_email_request): ?>
                        <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded-lg mb-4">
                            <p class="text-yellow-800 dark:text-yellow-200">
                                <?= __('pending_email_request', 'settings') ?>: <?= htmlspecialchars($pending_email_request['new_email']) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="space-y-4">
                            <?php echo csrf_input_field(); ?>
                            <input type="hidden" name="action" value="change_email">
                            
                            <div class="space-y-2">
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Current Email</label>
                                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                                       class="w-full px-3 py-2 border rounded-lg bg-gray-100 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm text-gray-700 dark:text-gray-300">New Email</label>
                                <input type="email" name="new_email" required
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Reason for Change</label>
                                <textarea name="reason" required rows="3"
                                          class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600"></textarea>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm text-gray-700 dark:text-gray-300">Current Password</label>
                                <input type="password" name="current_password" required
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                                Request Email Change
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- Password Change -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><?= __('change_password', 'settings') ?></h3>
                    <form method="POST" class="space-y-4">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="space-y-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-300"><?= __('current_password', 'settings') ?></label>
                            <input type="password" name="current_password" required
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-300"><?= __('new_password', 'settings') ?></label>
                            <input type="password" name="new_password" required minlength="8"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-300"><?= __('confirm_password', 'settings') ?></label>
                            <input type="password" name="confirm_password" required minlength="8"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <?= __('change_password', 'settings') ?>
                        </button>
                    </form>
                </div>
                
                <!-- Profile Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><?= __('profile_settings', 'settings') ?></h3>
                    <form method="POST" class="space-y-4">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="space-y-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-300"><?= __('timezone', 'profile') ?></label>
                            <select name="timezone" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                                <?php
                                $timezones = DateTimeZone::listIdentifiers();
                                foreach ($timezones as $tz) {
                                    $selected = $profile['timezone'] === $tz ? 'selected' : '';
                                    echo "<option value=\"$tz\" $selected>$tz</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-300"><?= __('company', 'profile') ?></label>
                            <input type="text" name="company" value="<?= htmlspecialchars($profile['company'] ?? '') ?>"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm text-gray-700 dark:text-gray-300"><?= __('phone', 'profile') ?></label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            <?= __('update_profile', 'settings') ?>
                        </button>
                    </form>
                </div>
                <!-- Account Activity -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><?= __('recent_activity', 'settings') ?></h3>
                    <div class="space-y-4">
                        <?php while ($login = $login_history->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-3 border rounded-lg dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <?= $login['success'] ? __('successful_login', 'settings') : __('failed_login', 'settings') ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    IP: <?= htmlspecialchars($login['ip_address']) ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <?= date('M j, Y H:i', strtotime($login['attempted_at'])) ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($login['user_agent']) ?>
                                </p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
              
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><?= __('active_sessions', 'settings') ?></h3>
                    <div class="space-y-4">
                        <?php
                        $stmt = $conn->prepare("
                            SELECT * FROM active_sessions 
                            WHERE user_id = ? AND expires > NOW()
                            ORDER BY created_at DESC
                        ");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $sessions = $stmt->get_result();
                        
                        while ($session = $sessions->fetch_assoc()):
                        ?>
                        <div class="flex items-center justify-between p-3 border rounded-lg dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($session['device_type']) ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($session['ip_address']) ?>
                                </p>
                            </div>
                            <form method="POST" class="inline">
                                <?php echo csrf_input_field(); ?>
                                <input type="hidden" name="action" value="terminate_session">
                                <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Terminate
                                </button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: #dc2626;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dark mode from PHP session
    if (<?= isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'true' : 'false' ?>) {
        document.documentElement.classList.add('dark');
    }

    // Listen for form submission
    const preferencesForm = document.querySelector('form[action="settings.php"]');
    preferencesForm.addEventListener('submit', function() {
        const darkModeEnabled = this.querySelector('input[name="dark_mode"]').checked;
        document.documentElement.classList.toggle('dark', darkModeEnabled);
    });
});
</script>
<?php include 'footer.php'; ?>