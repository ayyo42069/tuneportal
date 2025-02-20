<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Get user profile information from user_profiles table
$profile = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT up.profile_picture, u.username, u.role 
                           FROM users u 
                           LEFT JOIN user_profiles up ON u.id = up.user_id 
                           WHERE u.id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
}

// Set default profile picture if none exists
$profile_picture = isset($profile['profile_picture']) && !empty($profile['profile_picture']) 
    ? htmlspecialchars($profile['profile_picture']) 
    : 'default.png';
?>
<nav class="flex-grow overflow-y-auto">
        <div class="p-4 space-y-4">
            <!-- Profile Section -->
            <div class="relative">
                <button id="profileDropdown" class="flex items-center space-x-3 w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <img src="uploads/profiles/<?= $profile_picture ?>" 
                         alt="Profile" 
                         class="w-10 h-10 rounded-full object-cover"
                         onerror="this.src='uploads/profiles/default.png'">
                    <div class="flex flex-col text-left">
                        <span class="font-medium"><?= htmlspecialchars($profile['username'] ?? $_SESSION['username']) ?></span>
                        <span class="text-xs text-gray-500"><?= __('my_profile', 'sidebar') ?></span>
                    </div>
                </button>
                <!-- Dropdown Menu -->
                <div id="profileMenu" class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5">
                    <div class="py-1">
                        <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                            <p class="font-bold"><?= htmlspecialchars($_SESSION['username']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?= ucfirst($_SESSION['role']) ?></p>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-600">
                        <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                            <?= __('settings', 'sidebar') ?>
                        </a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                            <?= __('logout', 'sidebar') ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Common User Links -->
            <div class="space-y-2">
                <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'dashboard.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 011-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span><?= __('dashboard', 'sidebar') ?></span>
                </a>
                <a href="files.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'files.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span><?= __('my_files', 'sidebar') ?></span>
                </a>
                <a href="credits.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'credits.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?= __('credits', 'sidebar') ?></span>
                </a>
                <a href="tools.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'tools.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span><?= __('tools', 'sidebar') ?></span>
                </a>
            </div>

            <!-- Admin Section with improved styling -->
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-4 text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider"><?= __('admin_tools', 'sidebar') ?></p>
                    <div class="mt-3 space-y-2">
                        <a href="admin_credits.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'admin_credits.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span><?= __('manage_credits', 'sidebar') ?></span>
                        </a>
                        <a href="admin_tools.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'admin_tools.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1V4z"/>
                            </svg>
                            <span><?= __('manage_tools', 'sidebar') ?></span>
                        </a>
                        <a href="admin_files.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'admin_files.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            <span><?= __('manage_files', 'sidebar') ?></span>
                        </a>
                        <a href="admin_users.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'admin_users.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span><?= __('manage_users', 'sidebar') ?></span>
                        </a>
                        <a href="manage_tunes.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'admin_tunes.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span><?= __('manage_tune_options', 'sidebar') ?></span>
                        </a>
                        <a href="admin_notifications.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'manage_notifications.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span><?= __('manage_notifications', 'sidebar') ?></span>
                        </a>
                        <a href="admin_logs.php" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 <?php echo $current_page == 'admin_logs.php' ? 'bg-gray-100 dark:bg-gray-700 text-red-600 dark:text-red-400' : ''; ?>">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span><?= __('system_logs', 'sidebar') ?></span>
    </a>
                        <a href="/phpmyadmin" class="flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                            <span><?= __('phpmyadmin', 'sidebar') ?></span>
                        </a>
                       
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileDropdown = document.getElementById('profileDropdown');
    const profileMenu = document.getElementById('profileMenu');
    const darkModeToggle = document.getElementById('darkModeToggle');

    // Toggle profile dropdown
    profileDropdown.addEventListener('click', function() {
        profileMenu.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!profileDropdown.contains(event.target) && !profileMenu.contains(event.target)) {
            profileMenu.classList.add('hidden');
        }
    });

});
</script>

