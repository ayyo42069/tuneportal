<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TunePortal - Automotive Tuning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        colors: {
          // Define your black and white color palette
          primary: "#000000", // Black
          secondary: "#FFFFFF", // White
          accent: "#808080", // Gray (optional, for subtle contrast)
          neutral: "#F0F0F0", // Light gray for backgrounds
          text: "#333333", // Dark gray for text on light backgrounds
          textLight: "#FFFFFF", // White for text on dark backgrounds
        },
        fontFamily: {
          sans: ["Inter", "sans-serif"],
          serif: ["Merriweather", "serif"],
        },
      },
    },
  };
</script>
<style type="text/tailwindcss">
  @layer base {
    body {
      @apply bg-neutral text-text; /* Set default background and text */
    }
  }

  @layer components {
    .card {
      @apply bg-secondary dark:bg-gray-800 rounded-xl shadow-sm p-6;
    }

    .form-input {
      @apply w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 dark:bg-gray-700 dark:text-white;
    }

    .btn-primary {
      @apply bg-primary text-textLight px-6 py-2.5 rounded-lg hover:bg-gray-800 transition-colors inline-flex items-center gap-2;
    }

    .table-header {
      @apply px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider;
    }

    .table-row {
      @apply hover:bg-gray-50 dark:hover:bg-gray-700;
    }

    .notification-new {
      @apply bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200;
    }

    .notification-read {
      @apply bg-gray-50 dark:bg-gray-700;
    }

    .notification-unread {
      @apply bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700;
    }

    .notification-link {
      @apply text-primary dark:text-secondary hover:text-gray-800 dark:hover:text-gray-300;
    }
  }

  @layer utilities {
    .glassmorphism {
      @apply bg-white bg-opacity-10 backdrop-filter backdrop-blur-lg;
    }
  }
</style>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
<header class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white fixed w-full top-0 z-50 transition-all duration-300 shadow-md">
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
            <?php if(isset($_SESSION['user_id'])): ?>
            <button id="sidebar-toggle" class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors mr-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <?php endif; ?>
            
            <a href="/" class="text-2xl font-bold text-primary hover:text-secondary transition-colors flex items-center space-x-2">
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
            <?php endif; ?>
        </nav>

        <!-- Mobile Menu Button -->
        <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" id="mobile-menu-button">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>
</div>

<!-- Mobile Menu -->
<div class="md:hidden hidden bg-white dark:bg-gray-800" id="mobile-menu">
    <div class="container mx-auto px-4 py-4 space-y-2">
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Register</a>
            <a href="login.php" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Login</a>
        <?php endif; ?>
    </div>
</div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Close sidebar on window resize if screen becomes larger
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) { // lg breakpoint
            sidebar.classList.remove('-translate-x-full');
            mobileMenu.classList.add('hidden');
        }
    });
});
</script>

