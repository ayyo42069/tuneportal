<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunePortal - Automotive Tuning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(15px); }
        }
        .float {
            animation: float 12s ease-in-out infinite;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up {
            opacity: 0;
            animation: fadeUp 1s ease-out forwards;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        orbitron: ['Orbitron', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="flex flex-col min-h-screen bg-gray-900 text-white font-orbitron">
<header class="bg-transparent text-white fixed w-full top-0 z-50 transition-all duration-300">
    <div class="container mx-auto">
        <div class="flex justify-between items-center h-16 px-4 lg:px-6">
            <!-- Sidebar Toggle for Mobile -->
            <?php if(isset($_SESSION['user_id'])): ?>
            <button id="sidebar-toggle" class="lg:hidden p-2 hover:bg-red-700 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <?php endif; ?>
            
            <a href="/" class="text-2xl font-bold hover:text-red-400 transition-colors flex items-center space-x-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>TunePortal</span>
            </a>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-6">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center space-x-4">
                        <span class="text-red-300">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                        <a href="dashboard.php" class="hover:text-red-400 transition-colors px-3 py-2 rounded-lg hover:bg-red-700/50">Dashboard</a>
                        <a href="logout.php" class="hover:text-red-400 transition-colors px-3 py-2 rounded-lg hover:bg-red-700/50">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="register.php" class="hover:text-red-400 transition-colors px-3 py-2 rounded-lg hover:bg-red-700/50">Register</a>
                    <a href="login.php" class="hover:text-red-400 transition-colors px-3 py-2 rounded-lg hover:bg-red-700/50">Login</a>
                <?php endif; ?>
            </nav>

            <!-- Mobile Menu Button -->
            <button class="md:hidden p-2 hover:bg-red-700/50 rounded-lg" id="mobile-menu-button">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden hidden bg-gray-800/80 backdrop-blur-sm" id="mobile-menu">
        <div class="container mx-auto px-4 py-4 space-y-2">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="block px-4 py-2 hover:bg-red-600/50 rounded-lg transition-colors">Dashboard</a>
                <a href="logout.php" class="block px-4 py-2 hover:bg-red-600/50 rounded-lg transition-colors">Logout</a>
            <?php else: ?>
                <a href="register.php" class="block px-4 py-2 hover:bg-red-600/50 rounded-lg transition-colors">Register</a>
                <a href="login.php" class="block px-4 py-2 hover:bg-red-600/50 rounded-lg transition-colors">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
