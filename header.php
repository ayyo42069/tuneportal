<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunePortal - Automotive Tuning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hexagon-bg {
            background-image: 
                linear-gradient(30deg, rgba(255,255,255,0.05) 12%, transparent 12%, transparent 88%, rgba(255,255,255,0.05) 88%),
                linear-gradient(150deg, rgba(255,255,255,0.05) 12%, transparent 12%, transparent 88%, rgba(255,255,255,0.05) 88%);
            background-size: 50px 100px;
            transform: rotate(-30deg);
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-50">
<header class="bg-gradient-to-r from-gray-900 to-red-900 text-white fixed w-full top-0 z-50 shadow-md transition-all duration-300 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute -top-20 -left-10 w-full h-full hexagon-bg"></div>
    </div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="flex justify-between items-center h-16">
            <a href="/" class="text-2xl font-bold hover:text-red-300 transition-colors bg-clip-text text-transparent bg-gradient-to-r from-red-400 to-blue-500">TunePortal</a>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-6">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-300">Welcome, <span class="text-red-300"><?= $_SESSION['username'] ?></span></span>
                    <a href="dashboard.php" class="hover:text-blue-300 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="logout.php" class="hover:text-red-300 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </a>
                <?php else: ?>
                    <a href="register.php" class="hover:text-blue-300 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Register
                    </a>
                    <a href="login.php" class="hover:text-red-300 transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Login
                    </a>
                <?php endif; ?>
            </nav>

            <!-- Mobile Menu Button -->
            <button class="md:hidden p-2 hover:bg-gray-800/30 rounded-full transition-colors" id="mobile-menu-button">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden hidden bg-gradient-to-r from-gray-900 to-red-900" id="mobile-menu">
        <div class="container mx-auto px-4 py-4 space-y-4">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="block hover:text-blue-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                <a href="logout.php" class="block hover:text-red-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </a>
            <?php else: ?>
                <a href="register.php" class="block hover:text-blue-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Register
                </a>
                <a href="login.php" class="block hover:text-red-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    // Mobile Menu Toggle
    document.getElementById('mobile-menu-button').addEventListener('click', () => {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });

    // Sticky Header Animation
    window.addEventListener('scroll', () => {
        const header = document.querySelector('header');
        header.classList.toggle('shadow-2xl', window.scrollY > 100);
        header.style.transform = window.scrollY > 100 ? 'translateY(-10px)' : 'translateY(0)';
    });
</script>