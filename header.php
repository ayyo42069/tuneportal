<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- SEO Meta Tags -->
        <title>TunePortal: Unleash Your Car's True Potential</title>
    <meta name="description"
        content="Experience the pinnacle of automotive performance with our cutting-edge ECU tuning solutions. Unlock more horsepower, improve fuel economy, and optimize your driving experience in Budapest.">
    <meta name="keywords" content="ECU tuning, car tuning, performance upgrades, horsepower, fuel economy, automotive performance, engine tuning, dyno tuning, custom mapping, Budapest, Hungary">
    <meta name="author" content="Kristoffor">

    <!-- Robots Meta Tags -->
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">

    <!-- Open Graph / Facebook Meta Tags -->
    <meta property="og:title" content="TunePortal: Unleash Your Car's True Potential">
    <meta property="og:description"
        content="Experience the pinnacle of automotive performance with our cutting-edge ECU tuning solutions in Budapest.">
    <meta property="og:image" content="https://source.unsplash.com/1200x630/?sports-car">
    <meta property="og:url" content="https://tuneportal.germanywestcentral.cloudapp.azure.com">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TunePortal Co">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="TunePortal: Unleash Your Car's True Potential">
    <meta name="twitter:description"
        content="Experience the pinnacle of automotive performance with our cutting-edge ECU tuning solutions in Budapest.">
    <meta name="twitter:image" content="https://source.unsplash.com/1200x630/?sports-car">
    <meta name="twitter:url" content="https://tuneportal.germanywestcentral.cloudapp.azure.com">
    <meta name="twitter:site" content="@tuneportal">
    <meta name="twitter:creator" content="@tuneportal">

    <!-- Dublin Core Metadata (Optional) -->
    <meta name="DC.title" content="TunePortal: Unleash Your Car's True Potential">
    <meta name="DC.description"
        content="Experience the pinnacle of automotive performance with our cutting-edge ECU tuning solutions in Budapest.">
    <meta name="DC.creator" content="Kristoffor">
    <meta name="DC.publisher" content="TunePortal Co">
    <meta name="DC.date" content="<?= date('Y-m-d'); ?>">
    <meta name="DC.language" content="en">

    <!-- Other Meta Tags -->
    <meta name="revisit-after" content="7 days">
    <meta name="language" content="English">
    <meta name="geo.region" content="HU">
    <meta name="geo.placename" content="Budapest, Hungary">
    <meta name="geo.position" content="47.4979;19.0402">
    <meta name="ICBM" content="47.4979, 19.0402">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/countup.js/2.0.7/countUp.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <!-- Favicon (replace with your own) -->
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#ff4d4d',
                        secondary: '#3d3d3d',
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .glassmorphism {
                @apply bg-white bg-opacity-10 backdrop-filter backdrop-blur-lg;
            }
        }

        /* Custom CSS (example - adjust as needed) */
        .animate-fade-in-up {
            animation: fadeInUp 1s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <header
        class="bg-white dark:bg-gray-800 text-gray-800 dark:text-white fixed w-full top-0 z-50 transition-all duration-300 shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <button id="sidebar-toggle"
                        class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors mr-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <?php endif; ?>

                    <a href="/"
                        class="text-2xl font-bold text-primary hover:text-secondary transition-colors flex items-center space-x-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>TunePortal</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-6">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php"
                        class="hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Register</a>
                    <a href="login.php"
                        class="hover:text-primary transition-colors px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Login</a>
                    <?php endif; ?>
                </nav>

                <!-- Mobile Menu Button -->
                <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    id="mobile-menu-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-white dark:bg-gray-800" id="mobile-menu">
            <div class="container mx-auto px-4 py-4 space-y-2">
                <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Register</a>
                <a href="login.php"
                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">Login</a>
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
