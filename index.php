<?php 
include 'config.php'; // Add database connection
include 'header.php'; // Remove duplicate header inclusion

// Add error handling for database queries
try {
    // Fetch statistics
    $stats = $conn->query("
        SELECT 
            (SELECT COUNT(*) FROM files WHERE status = 'processed') as tuned_files,
            (SELECT COUNT(DISTINCT user_id) FROM files WHERE status = 'processed') as active_tuners,
            (SELECT COUNT(*) FROM file_versions) as total_tunes,
            (SELECT COUNT(DISTINCT car_model) FROM files) as unique_models
    ");

    if (!$stats) {
        throw new Exception("Error fetching statistics");
    }
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

    if (!$latest_tunes) {
        throw new Exception("Error fetching latest tunes");
    }

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

    if (!$top_tuners) {
        throw new Exception("Error fetching top tuners");
    }

} catch (Exception $e) {
    // Set default values if database queries fail
    $stats = [
        'tuned_files' => 0,
        'active_tuners' => 0,
        'total_tunes' => 0,
        'unique_models' => 0
    ];
    error_log("Database error: " . $e->getMessage());
}
?>

<main class="flex-grow">
    <!-- Hero Section -->
    <section class="relative min-h-screen bg-gradient-to-br from-slate-900 via-primary to-secondary overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute inset-0">
            <div id="particles-js" class="absolute inset-0 opacity-30"></div>
            <div class="absolute inset-0 bg-[url('/assets/images/circuit-pattern.png')] opacity-5"></div>
        </div>
        
        <!-- Hero Content -->
        <div class="relative z-10 container mx-auto px-4 h-screen flex items-center">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column -->
                <div class="text-white space-y-8">
                    <h1 class="text-5xl lg:text-7xl font-bold leading-tight animate-fade-in-up">
                        Next-Gen<br>
                        <span class="bg-gradient-to-r from-red-500 to-orange-500 text-transparent bg-clip-text">
                            ECU Tuning
                        </span>
                    </h1>
                    <p class="text-xl text-gray-300 max-w-xl animate-fade-in-up animation-delay-300">
                        Transform your vehicle's performance with precision-engineered tuning solutions backed by advanced technology.
                    </p>
                    <div class="flex gap-4 animate-fade-in-up animation-delay-600">
                        <?php if(!isset($_SESSION['user_id'])): ?>
                            <a href="register.php" 
                               class="px-8 py-4 bg-red-600 hover:bg-red-700 rounded-lg font-semibold 
                                      transition-all duration-300 transform hover:scale-105 shadow-lg shadow-red-600/30">
                                Start Tuning Now
                            </a>
                        <?php endif; ?>
                        <a href="#features" 
                           class="px-8 py-4 border border-white/30 hover:bg-white/10 rounded-lg font-semibold 
                                  transition-all duration-300">
                            Learn More
                        </a>
                    </div>
                </div>

                <!-- Right Column - Stats Card -->
                <div class="backdrop-blur-xl bg-white/10 rounded-2xl p-8 border border-white/10 shadow-2xl">
                    <div class="grid grid-cols-2 gap-8">
                        <div class="text-center p-4 bg-white/5 rounded-xl">
                            <div class="text-3xl font-bold text-white count-up" data-count="<?= $stats['tuned_files'] ?>">0</div>
                            <div class="text-gray-400 mt-2">Tuned Vehicles</div>
                        </div>
                        <div class="text-center p-4 bg-white/5 rounded-xl">
                            <div class="text-3xl font-bold text-white count-up" data-count="<?= $stats['active_tuners'] ?>">0</div>
                            <div class="text-gray-400 mt-2">Expert Tuners</div>
                        </div>
                        <div class="text-center p-4 bg-white/5 rounded-xl">
                            <div class="text-3xl font-bold text-white count-up" data-count="<?= $stats['total_tunes'] ?>">0</div>
                            <div class="text-gray-400 mt-2">Total Tunes</div>
                        </div>
                        <div class="text-center p-4 bg-white/5 rounded-xl">
                            <div class="text-3xl font-bold text-white count-up" data-count="<?= $stats['unique_models'] ?>">0</div>
                            <div class="text-gray-400 mt-2">Car Models</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-slate-900">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">Why Choose TunePortal?</h2>
                <div class="w-24 h-1 bg-red-600 mx-auto rounded-full"></div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature Cards with enhanced styling -->
                <div class="group p-8 bg-slate-800 rounded-2xl hover:bg-slate-700/50 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-red-600/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-red-600/20 transition-colors">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-white">Advanced ECU Tuning</h3>
                    <p class="text-gray-400">State-of-the-art performance optimization with real-time monitoring and adjustments.</p>
                </div>
                <!-- ... Similar styling for other feature cards ... -->
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-24 bg-gradient-to-br from-red-600 to-orange-600 overflow-hidden">
        <div class="absolute inset-0 bg-[url('/assets/images/noise.png')] opacity-10"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-8">Ready to Transform Your Vehicle?</h2>
                <p class="text-xl text-white/80 mb-12">
                    Join thousands of satisfied customers who have unlocked their vehicle's true potential
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="register.php" 
                       class="px-8 py-4 bg-white text-red-600 rounded-lg font-semibold 
                              hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                        Get Started Now
                    </a>
                    <a href="#contact" 
                       class="px-8 py-4 border border-white text-white hover:bg-white/10 
                              rounded-lg font-semibold transition-colors">
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<!-- Keep your existing Particle.js and count-up scripts -->
<script>
    // Particle.js configuration
    particlesJS("particles-js", {
        particles: {
            number: { value: 80, density: { enable: true, value_area: 800 } },
            color: { value: "#ffffff" },
            shape: { type: "circle" },
            opacity: { value: 0.5, random: false },
            size: { value: 3, random: true },
            line_linked: { enable: true, distance: 150, color: "#ffffff", opacity: 0.4, width: 1 },
            move: { enable: true, speed: 6, direction: "none", random: false, straight: false, out_mode: "out", bounce: false }
        },
        interactivity: {
            detect_on: "canvas",
            events: { onhover: { enable: true, mode: "repulse" }, onclick: { enable: true, mode: "push" }, resize: true },
            modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
        },
        retina_detect: true
    });

    // Animate count-up
    const countUpElements = document.querySelectorAll('.count-up');
    countUpElements.forEach(element => {
        const target = parseInt(element.getAttribute('data-count'), 10);
        let count = 0;
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60 FPS

        const updateCount = () => {
            count += increment;
            if (count < target) {
                element.textContent = Math.round(count);
                requestAnimationFrame(updateCount);
            } else {
                element.textContent = target;
            }
        };

        updateCount();
    });
</script>


