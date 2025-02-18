<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar" class="w-64 h-screen fixed top-0 left-0 lg:translate-x-0 -translate-x-full transition-transform duration-300 bg-white dark:bg-gray-800 text-gray-800 dark:text-white z-40 mt-16 flex flex-col shadow-lg">
    <nav class="flex-grow overflow-y-auto">
        <div class="p-4 space-y-4">
            <!-- Profile Section -->
            <div class="relative">
                <button id="profileDropdown" class="flex items-center space-x-3 w-full p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <img src="https://via.placeholder.com/40" alt="Profile" class="w-10 h-10 rounded-full">
                    <span class="font-medium">My Profile</span>
                </button>
                <div id="profileMenu" class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 hidden">
                    <div class="py-1">
                        <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Settings</a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Logout</a>
                    </div>
                </div>
            </div>
            <!-- Rest of your sidebar items -->
        </div>
    </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileDropdown = document.getElementById('profileDropdown');
    const profileMenu = document.getElementById('profileMenu');

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

