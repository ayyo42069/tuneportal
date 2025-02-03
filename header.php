<?php session_start(); ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunePortal - Automotive Tuning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen">
<header class="bg-red-600 text-white fixed w-full top-0 z-50 shadow-md transition-all duration-300">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <a href="/" class="text-2xl font-bold hover:text-red-200 transition-colors">TunePortal</a>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-6">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="text-red-200">Welcome, <?= $_SESSION['username'] ?></span>
                    <a href="dashboard.php" class="hover:text-red-200 transition-colors">Dashboard</a>
                    <a href="logout.php" class="hover:text-red-200 transition-colors">Logout</a>
                <?php else: ?>
                    <a href="register.php" class="hover:text-red-200 transition-colors">Register</a>
                    <a href="login.php" class="hover:text-red-200 transition-colors">Login</a>
                <?php endif; ?>
            </nav>

            <!-- Mobile Menu Button -->
            <button class="md:hidden p-2" id="mobile-menu-button">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden hidden bg-red-700" id="mobile-menu">
        <div class="container mx-auto px-4 py-4 space-y-4">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="block hover:text-red-200">Dashboard</a>
                <a href="logout.php" class="block hover:text-red-200">Logout</a>
            <?php else: ?>
                <a href="register.php" class="block hover:text-red-200">Register</a>
                <a href="login.php" class="block hover:text-red-200">Login</a>
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
        header.classList.toggle('shadow-lg', window.scrollY > 100);
    });
</script>