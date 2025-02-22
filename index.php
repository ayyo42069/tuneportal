<?php
include 'config.php'; // Database connection

// Fetch statistics
try {
    $stats = $conn->query("
    SELECT 
        COALESCE((SELECT COUNT(*) FROM files WHERE status = 'processed'), 0) as tuned_files,
        COALESCE((SELECT COUNT(DISTINCT user_id) FROM files WHERE status = 'processed'), 0) as active_tuners,
        COALESCE((SELECT COUNT(*) FROM file_versions), 0) as total_tunes,
        COALESCE((SELECT COUNT(DISTINCT car_model) FROM files), 0) as unique_models
");
$stats = $stats->fetch_assoc();

    // Fetch latest successful tunes
    $latest_tunes = $conn->query("
        SELECT f.title, f.car_model, u.username, fv.uploaded_at 
        FROM files f 
        JOIN users u ON f.user_id = u.id 
        JOIN file_versions fv ON f.id = fv.file_id 
        WHERE f.status = 'processed' 
        ORDER BY fv.uploaded_at DESC 
        LIMIT 5
    ");

    // Fetch top tuners
    $top_tuners = $conn->query("
        SELECT u.username, u.id, COUNT(f.id) as tune_count 
        FROM users u 
        JOIN files f ON u.id = f.user_id 
        WHERE f.status = 'processed' 
        GROUP BY u.id 
        ORDER BY tune_count DESC 
        LIMIT 3
    ");
} catch (Exception $e) {
    $stats = [
        'tuned_files' => 0,
        'active_tuners' => 0,
        'total_tunes' => 0,
        'unique_models' => 0
    ];
    error_log("Database error: " . $e->getMessage());
}
?>
<?php include 'header.php'; ?>
<main class="flex-grow pt-0">
   
    <!-- Hero Section -->
    <section class="relative min-h-screen pt-16 flex items-center justify-center">
        <!-- Particles Background -->
       
        
        <!-- Video Background -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/80 to-black/70 z-10"></div>
            <video class="w-full h-full object-cover" autoplay muted loop playsinline>
                <source src="/src/videos/car-tuning.mp4" type="video/mp4">
            </video>
        </div>

        <!-- Hero Content Container -->
        <div class="relative z-20 container mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center w-full">
                <!-- Left Column - Content -->
                <div class="glass-hero p-6 sm:p-8 lg:p-10 rounded-3xl text-white space-y-8 backdrop-blur-lg 
                            bg-black/20 border border-white/10 order-2 lg:order-1">
                    <div class="inline-flex items-center space-x-2 px-4 py-2 bg-red-600/20 backdrop-blur-sm rounded-full">
                        <span class="animate-pulse w-2 h-2 bg-red-500 rounded-full"></span>
                        <span class="text-red-400 font-semibold">Professional ECU Tuning Solutions</span>
                    </div>

                    <div class="space-y-6">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight">
                            Unleash Your
                            <span class="relative inline-block">
                                <span class="bg-gradient-to-r from-red-500 via-orange-500 to-red-500 text-transparent bg-clip-text">Vehicle's</span>
                                <svg class="absolute -bottom-2 sm:-bottom-3 lg:-bottom-4 left-0 w-full" viewBox="0 0 100 12" preserveAspectRatio="none">
                                    <path d="M0,0 Q50,12 100,0" stroke="url(#gradient)" stroke-width="4" fill="none"/>
                                    <defs>
                                        <linearGradient id="gradient">
                                            <stop offset="0%" stop-color="#EF4444"/>
                                            <stop offset="50%" stop-color="#F97316"/>
                                            <stop offset="100%" stop-color="#EF4444"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </span>
                            <br class="hidden sm:block">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-200">Potential</span>
                        </h1>

                        <p class="text-lg sm:text-xl text-gray-300 max-w-xl">
                            Experience precision engineering and cutting-edge technology to maximize your vehicle's performance.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 pt-4">
                            <?php if(!isset($_SESSION['user_id'])): ?>
                                <a href="register.php" 
                                   class="group relative px-8 py-4 bg-gradient-to-r from-red-600 to-red-700 rounded-xl 
                                          font-semibold inline-flex items-center justify-center overflow-hidden">
                                    <span class="relative z-10 flex items-center text-white">
                                        Start Tuning Now
                                        <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-orange-500 opacity-0 
                                              group-hover:opacity-100 transition-opacity duration-300"></div>
                                </a>
                            <?php endif; ?>
                            <a href="#features" 
                               class="group px-8 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl 
                                      font-semibold inline-flex items-center justify-center hover:bg-white/20 transition-all">
                                <span class="text-white flex items-center">
                                    Explore Features
                                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-y-1 transition-transform" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

              
                          <!-- Right Column - Stats -->
                          
                          <div class="glass-hero p-6 sm:p-8 lg:p-10 rounded-3xl text-white space-y-8 backdrop-blur-lg 
                            bg-black/20 border border-white/10 order-2 lg:order-1">
                    <div class="stats-grid-card rounded-2xl p-6 h-full">
                        <div class="grid grid-cols-2 gap-4">
                            <?php
                            $statsData = [
                                [
                                    'count' => $stats['tuned_files'], 
                                    'label' => 'Tuned Vehicles', 
                                    'icon' => 'M8.5 3.5l4.5 11h2l4.5-11M4 20h3.6l1.2-3h6.4l1.2 3H20M6 16l2-3m8 3l2-3M3 20h18M6.2 20h11.6M11 3.5h2M9.5 3.5C9.5 2.7 10.1 2 11 2h2c.9 0 1.5.7 1.5 1.5'
                                ],
                                [
                                    'count' => $stats['active_tuners'], 
                                    'label' => 'Expert Tuners', 
                                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
                                ],
                                [
                                    'count' => $stats['total_tunes'], 
                                    'label' => 'Total Tunes', 
                                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7m2-4l2 2m-2-2l-2 2m8 8l-2-2m2 2l2-2M3 21l2-2m-2 2l-2-2'
                                ],
                                [
                                    'count' => $stats['unique_models'], 
                                    'label' => 'Car Models', 
                                    'icon' => 'M17 16v2a2 2 0 01-2 2H9a2 2 0 01-2-2v-2m2-4h6m-6 0a2 2 0 00-2 2v2m0-4a2 2 0 012-2h6a2 2 0 012 2v2m0-4V8a2 2 0 00-2-2H9a2 2 0 00-2 2v2m12 0a2 2 0 00-2-2H9a2 2 0 00-2 2'
                                ]
                            ];

                            foreach ($statsData as $stat): ?>
                                <div class="group stat-item p-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="stat-icon">
                                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $stat['icon'] ?>"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="stat-number count-up" data-count="<?= $stat['count'] ?>">0</div>
                                            <div class="text-sm text-gray-400"><?= $stat['label'] ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <div class="p-2 rounded-full bg-white/10 backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Features Section with 3D Cards -->
    <section id="features" class="relative overflow-hidden section-spacing backdrop-blur-sm bg-gradient-to-b from-slate-900/90 to-black/90 dark:from-gray-900/90 dark:to-black/90">
        <div class="absolute inset-0 bg-[url('/src/images/grid-pattern.svg')] opacity-5"></div>
    <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-5xl font-bold text-white mb-6">Why Choose TunePortal?</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-red-500 to-orange-500 mx-auto rounded-full"></div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-12">
                <?php 
                $features = [
                    [
                        'title' => 'Advanced ECU Tuning',
                        'description' => 'State-of-the-art performance optimization with real-time monitoring and adjustments.',
                        'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'
                    ],
                    [
                        'title' => 'Real-Time Analytics',
                        'description' => 'Monitor your vehicle\'s performance in real-time with our advanced analytics dashboard.',
                        'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
                    ],
                    [
                        'title' => '24/7 Expert Support',
                        'description' => 'Our certified tuning experts are available around the clock to assist you with any issues.',
                        'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'
                    ]
                ];

                foreach ($features as $feature): ?>
                    <div class="group perspective">
                        <div class="relative transform transition-all duration-500 group-hover:rotate-y-12">
                            <div class="absolute inset-0 bg-gradient-to-r from-red-500/30 to-orange-500/30 rounded-2xl transform -rotate-y-12 group-hover:rotate-y-0 transition-all duration-500 opacity-0 group-hover:opacity-100 backdrop-blur-lg"></div>
                            <div class="glass-feature relative rounded-2xl p-8 transform group-hover:rotate-y-12 transition-all duration-500">
                                <div class="w-16 h-16 bg-red-600/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600/20 transition-colors">
                                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $feature['icon'] ?>"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold mb-4 text-white"><?= $feature['title'] ?></h3>
                                <p class="text-gray-400"><?= $feature['description'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Latest Tunes Section with Animated Cards -->
    <section id="latest-tunes" class="relative overflow-hidden section-spacing bg-black dark:bg-gray-900">
    <div class="absolute inset-0 bg-[url('/src/images/texture.jpg')] opacity-5"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-5xl font-bold text-white mb-6">Latest Successful Tunes</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-red-500 to-orange-500 mx-auto rounded-full"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php while ($tune = $latest_tunes->fetch_assoc()): ?>
                    <div class="group bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 
                                transform hover:scale-105 transition-all duration-500 hover:shadow-2xl hover:shadow-red-500/20">
                        <div class="flex items-center justify-between mb-6">
                            <div class="bg-red-600/20 rounded-lg p-3">
                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <span class="text-gray-400 text-sm"><?= date('M d, Y', strtotime($tune['uploaded_at'])) ?></span>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3 group-hover:text-red-500 transition-colors">
        <?= htmlspecialchars($tune['title'] ?? '') ?>
    </h3>
    <p class="text-gray-400 mb-4"><?= htmlspecialchars($tune['car_model'] ?? '') ?></p>
       <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="ml-3 text-gray-400">Tuned by <span class="text-red-500 font-medium">
        <?= htmlspecialchars($tune['username'] ?? '') ?></span>
    </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Enhanced CTA Section (continuing) -->
    <section class="relative overflow-hidden section-spacing bg-gradient-to-br from-red-600 to-orange-600 dark:from-red-700 dark:to-orange-700">
                <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-5xl md:text-6xl font-bold text-white mb-8">Ready to Transform Your Vehicle?</h2>
                <p class="text-xl text-white/80 mb-12 max-w-2xl mx-auto">
                    Join thousands of satisfied customers who have unlocked their vehicle's true potential with our professional tuning solutions.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" 
                           class="group px-8 py-4 bg-white text-red-600 rounded-lg font-semibold inline-flex items-center justify-center
                                  hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                            Get Started Now
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                    <a href="#contact" 
                       class="group px-8 py-4 border-2 border-white text-white rounded-lg font-semibold inline-flex items-center justify-center
                              hover:bg-white hover:text-red-600 transition-all duration-300">
                        Contact Support
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Particle.js and Count-Up Scripts -->
<script>


    // Enhanced count-up animation with easing
    const countUpElements = document.querySelectorAll('.count-up');
    
    const easeOutQuart = x => 1 - Math.pow(1 - x, 4);
    
    countUpElements.forEach(element => {
        const target = parseInt(element.getAttribute('data-count'), 10);
        let startTime = null;
        const duration = 2000; // 2 seconds

        function animate(currentTime) {
            if (!startTime) startTime = currentTime;
            const progress = Math.min((currentTime - startTime) / duration, 1);
            const easedProgress = easeOutQuart(progress);
            const currentValue = Math.floor(easedProgress * target);
            
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.textContent = target.toLocaleString();
            }
        }

        // Start animation when element is in viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    requestAnimationFrame(animate);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        observer.observe(element);
    });
</script>

<?php include 'footer.php'; ?>