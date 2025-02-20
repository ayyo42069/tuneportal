<!DOCTYPE html>
<html lang="en" <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'class="dark"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunePortal - Automotive Tuning Platform</title>
    <link href="/src/css/tailwind.css" rel="stylesheet">
    <link href="/src/css/custom.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
<header class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white fixed w-full top-0 z-50 transition-all duration-300 shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <?php if(isset($_SESSION['user_id'])): ?>
                <button x-data x-on:click="$dispatch('toggle-sidebar')" class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors mr-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <?php endif; ?>
                
                <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : '/'; ?>" class="text-2xl font-bold text-primary hover:text-secondary transition-colors flex items-center space-x-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>TunePortal</span>
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-6">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Register</a>
                    <a href="login.php" class="hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Login</a>
                <?php else: ?>
                    <a href="dashboard.php" class="hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                    <a href="logout.php" class="hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                <?php endif; ?>
            </nav>

            <!-- Mobile Menu Button -->
            <button x-data x-on:click="$dispatch('toggle-menu')" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-data="{ open: false }" 
         x-on:toggle-menu.window="open = !open"
         x-show="open" 
         x-transition 
         class="md:hidden bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4 py-4 space-y-2">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Register</a>
                <a href="login.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Login</a>
            <?php else: ?>
                <a href="dashboard.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Dashboard</a>
                <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</header>