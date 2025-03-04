<!DOCTYPE html>
<html lang="en" <?php echo isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'class="dark"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuning Portal - Automotive Tuning Platform</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Tuning Portal - Your premier platform for automotive tuning solutions, custom maps, and professional tuning services">
    <meta name="keywords" content="automotive tuning, car tuning, ECU mapping, performance tuning, engine tuning">
    <meta name="author" content="Tuning Portal">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="Tuning Portal - Automotive Tuning Platform">
    <meta property="og:description" content="Professional automotive tuning solutions and services">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tuning-portal.eu">
    <meta property="og:image" content="https://tuning-portal.eu/logo.png">
    
    <!-- Existing CSS -->
    <link href="/src/css/tailwind.css" rel="stylesheet">
    <link href="/src/css/custom.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://www.googletagmanager.com">
    
    <!-- Performance Optimization -->
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preload" as="video" href="/src/videos/car-tuning.mp4" type="video/mp4">
    
    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1RS47DBT8C"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-1RS47DBT8C');
    </script>
    
    <!-- Existing Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Performance Enhancements -->
    <script>
        // Defer non-critical images
        document.addEventListener("DOMContentLoaded", function() {
            var lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));
            if ("IntersectionObserver" in window) {
                let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            let lazyImage = entry.target;
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.classList.remove("lazy");
                            lazyImageObserver.unobserve(lazyImage);
                        }
                    });
                });
                lazyImages.forEach(function(lazyImage) {
                    lazyImageObserver.observe(lazyImage);
                });
            }
        });

        // Add page load performance monitoring
        window.addEventListener('load', function() {
            setTimeout(function() {
                const timing = window.performance.timing;
                const pageLoadTime = timing.loadEventEnd - timing.navigationStart;
                console.log('Page load time:', pageLoadTime + 'ms');
                // Send to Analytics if needed
                if (typeof gtag === 'function') {
                    gtag('event', 'performance', {
                        'page_load_time': pageLoadTime
                    });
                }
            }, 0);
        });
    </script>
    
    <!-- Dark mode script (your existing one) -->
    <script>
        document.documentElement.classList.toggle(
            "dark",
            localStorage.theme === "dark" ||
            (!("theme" in localStorage) && window.matchMedia("(prefers-color-scheme: dark)").matches)
        );
    </script>
    
<!-- ... existing head content ... -->
    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://www.googletagmanager.com">
    
    <!-- Add these new preload tags -->
    <link rel="preload" as="video" href="/src/videos/car-tuning.mp4" type="video/mp4">
    
    <!-- Add this script before closing head tag -->
    <script>
        // Video lazy loading and optimization
        document.addEventListener('DOMContentLoaded', function() {
            const lazyVideos = [].slice.call(document.querySelectorAll("video.lazy-video"));
            
            if ("IntersectionObserver" in window) {
                const lazyVideoObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(video) {
                        if (video.isIntersecting) {
                            for (const source in video.target.children) {
                                const videoSource = video.target.children[source];
                                if (typeof videoSource.tagName === "string" && videoSource.tagName === "SOURCE") {
                                    videoSource.src = videoSource.dataset.src;
                                }
                            }

                            video.target.load();
                            video.target.classList.remove("lazy-video");
                            lazyVideoObserver.unobserve(video.target);
                        }
                    });
                });

                lazyVideos.forEach(function(lazyVideo) {
                    lazyVideoObserver.observe(lazyVideo);
                });
            }
        });
    </script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <header class="fixed w-full top-0 z-50 transition-all duration-300">
    <!-- Glassmorphism background -->
    <div class="absolute inset-0 backdrop-blur-lg bg-white/70 dark:bg-gray-900/70 border-b border-white/10 dark:border-gray-800/50"></div>
    
    <div class="container mx-auto px-4 relative">
        <div class="flex justify-between items-center h-20">
            <!-- Logo and Toggle Button -->
            <div class="flex items-center space-x-4">
                <?php if(isset($_SESSION['user_id'])): ?>
                <button x-data x-on:click="$dispatch('toggle-sidebar')" 
                        class="lg:hidden glass-button p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <?php endif; ?>
                
                <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : '/'; ?>" 
                   class="flex items-center space-x-3 group">
                    <div class="glass-icon p-2 rounded-xl">
                        <svg class="w-8 h-8 text-red-500 transform transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-red-500 to-orange-500 text-transparent bg-clip-text">
                    Tuning Portal 
                    </span>
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-6">
                <!-- Dark Mode Toggle -->
                <button onclick="toggleDarkMode()" class="glass-button p-2 rounded-xl">
                    <svg class="w-6 h-6 text-orange-500 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg class="w-6 h-6 text-slate-700 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" class="nav-link glass-button px-6 py-2 rounded-xl">Register</a>
                    <a href="login.php" class="nav-link glass-button-primary px-6 py-2 rounded-xl">Login</a>
                <?php else: ?>
                    <a href="dashboard.php" class="nav-link glass-button px-6 py-2 rounded-xl">Dashboard</a>
                    <a href="logout.php" class="nav-link glass-button-secondary px-6 py-2 rounded-xl">Logout</a>
                <?php endif; ?>
            </nav>

            <!-- Mobile Menu -->
            <div class="md:hidden flex items-center space-x-3">
                <button onclick="toggleDarkMode()" class="glass-button p-2 rounded-xl">
                    <svg class="w-6 h-6 text-orange-500 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg class="w-6 h-6 text-slate-700 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <button x-data x-on:click="$dispatch('toggle-menu')" class="glass-button p-2 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div x-data="{ open: false }" 
         x-on:toggle-menu.window="open = !open"
         x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="md:hidden relative">
        <div class="glass-dropdown container mx-auto px-4 py-4 space-y-2">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="mobile-nav-link block px-4 py-2 rounded-xl">Register</a>
                <a href="login.php" class="mobile-nav-link block px-4 py-2 rounded-xl">Login</a>
            <?php else: ?>
                <a href="dashboard.php" class="mobile-nav-link block px-4 py-2 rounded-xl">Dashboard</a>
                <a href="logout.php" class="mobile-nav-link block px-4 py-2 rounded-xl">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<div class="h-5"></div>
<script>
    function toggleDarkMode() {
        // Check current preference
        if (localStorage.theme === 'dark') {
            // Switch to light mode
            localStorage.theme = 'light'
            document.documentElement.classList.remove('dark')
        } else {
            // Switch to dark mode
            localStorage.theme = 'dark'
            document.documentElement.classList.add('dark')
        }

        // Send AJAX request to update backend
        fetch('update_theme.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ theme: localStorage.theme })
        });
    }
</script>