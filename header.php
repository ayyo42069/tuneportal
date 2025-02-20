
<!DOCTYPE html>
<html lang="en" <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'class="dark"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunePortal - Automotive Tuning Platform</title>
    <link href="/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

<header class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white fixed w-full top-0 z-50 transition-all duration-300 border-b border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-4">
                <?php if(isset($_SESSION['user_id'])): ?>
                <button x-data x-on:click="$dispatch('toggle-sidebar')" class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <?php endif; ?>
                
                <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : '/'; ?>" 
                   class="flex items-center space-x-3 text-red-600 dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-2xl font-bold">TunePortal</span>
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-6">
                <nav class="flex items-center space-x-4">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Register</a>
                        <a href="login.php" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors shadow-sm hover:shadow-md">Login</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Dashboard</a>
                        <a href="settings.php" class="px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Settings</a>
                        <a href="logout.php" class="px-4 py-2 text-red-600 dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors">Logout</a>
                    <?php endif; ?>
                </nav>
                
                <!-- Theme Toggle -->
                <button @click="toggleTheme()" 
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg x-show="!darkMode" class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center space-x-2">
                <button @click="toggleTheme()" 
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg x-show="!darkMode" class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                    </svg>
                </button>
                <button x-data x-on:click="$dispatch('toggle-menu')" 
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-data="{ open: false }" 
         x-on:toggle-menu.window="open = !open"
         x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="md:hidden border-t border-gray-200 dark:border-gray-700">
        <nav class="container mx-auto px-4 py-4 space-y-2">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Register</a>
                <a href="login.php" class="block px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-center shadow-sm hover:shadow-md">Login</a>
            <?php else: ?>
                <a href="dashboard.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Dashboard</a>
                <a href="settings.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Settings</a>
                <a href="logout.php" class="block px-4 py-2 text-red-600 dark:text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- Add spacing for fixed header -->
<div class="h-16"></div>