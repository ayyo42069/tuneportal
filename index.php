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
    <section class="relative min-h-screen bg-gradient-to-br from-gray-900 via-red-900 to-gray-900 overflow-hidden">
        <div id="particles-js" class="absolute inset-0 z-0 opacity-50"></div>
        
        <div class="relative z-10 h-full flex items-center py-32">
            <div class="container mx-auto px-4">
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16 max-w-5xl mx-auto">
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 text-white text-center transform hover:scale-105 transition-all duration-300">
                        <div class="text-3xl font-bold count-up mb-2" data-count="<?= $stats['tuned_files'] ?>">0</div>
                        <div class="text-sm text-gray-300">Tuned Vehicles</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 text-white text-center transform hover:scale-105 transition-all duration-300">
                        <div class="text-3xl font-bold count-up mb-2" data-count="<?= $stats['active_tuners'] ?>">0</div>
                        <div class="text-sm text-gray-300">Expert Tuners</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 text-white text-center transform hover:scale-105 transition-all duration-300">
                        <div class="text-3xl font-bold count-up mb-2" data-count="<?= $stats['total_tunes'] ?>">0</div>
                        <div class="text-sm text-gray-300">Total Tunes</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 text-white text-center transform hover:scale-105 transition-all duration-300">
                        <div class="text-3xl font-bold count-up mb-2" data-count="<?= $stats['unique_models'] ?>">0</div>
                        <div class="text-sm text-gray-300">Car Models</div>
                    </div>
                </div>

                <!-- Hero Content -->
                <div class="text-center max-w-4xl mx-auto">
                    <h1 class="text-5xl md:text-7xl font-bold mb-6 text-white animate-fade-in-up">
                        Unleash Your Car's<br>
                        <span class="bg-gradient-to-r from-red-500 to-red-300 text-transparent bg-clip-text">True Potential</span>
                    </h1>
                    <p class="text-xl mb-12 text-gray-300 animate-fade-in-up animation-delay-300 max-w-2xl mx-auto">
                        Experience professional-grade ECU tuning with our cutting-edge platform. 
                        Optimize performance, improve efficiency, and unlock hidden power.
                    </p>
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <div class="space-x-4 animate-fade-in-up animation-delay-600">
                            <a href="register.php" 
                               class="inline-block bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-lg text-lg font-semibold 
                                      transition-all duration-300 transform hover:scale-105 hover:shadow-xl">
                                Start Tuning Now
                            </a>
                            <a href="#features" 
                               class="inline-block bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-lg text-lg font-semibold 
                                      backdrop-blur-md transition-all duration-300">
                                Learn More
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800 dark:text-white">
                Why Choose TunePortal?
            </h2>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Feature Cards -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-red-500/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Advanced ECU Tuning</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Professional-grade tuning tools and real-time optimization for maximum performance gains.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-red-500/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Safe & Secure</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Built-in safety measures and comprehensive diagnostics to protect your vehicle.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-red-500/10 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Expert Community</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        Connect with professional tuners and enthusiasts for support and guidance.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Tunes Section -->
    <?php if ($latest_tunes && $latest_tunes->num_rows > 0): ?>
    <section class="py-24 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800 dark:text-white">Latest Tunes</h2>
            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <?php while ($tune = $latest_tunes->fetch_assoc()): ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 shadow-lg">
                    <h3 class="font-bold text-xl mb-2 text-gray-800 dark:text-white"><?= htmlspecialchars($tune['title']) ?></h3>
                    <p class="text-gray-600 dark:text-gray-300"><?= htmlspecialchars($tune['car_model']) ?></p>
                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>By <?= htmlspecialchars($tune['username']) ?></span>
                        <span class="ml-4"><?= date('M j, Y', strtotime($tune['uploaded_at'])) ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="relative py-24 bg-gradient-to-br from-red-600 to-red-800 text-white">
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-8">Ready to Transform Your Vehicle?</h2>
                <p class="text-xl mb-12 text-red-100">
                    Join thousands of satisfied users who have already unlocked their vehicle's true potential.
                </p>
                <div class="space-x-4">
                    <a href="register.php" 
                       class="inline-block bg-white text-red-600 px-8 py-4 rounded-lg text-lg font-semibold 
                              hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                        Get Started Now
                    </a>
                    <a href="#features" 
                       class="inline-block border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold 
                              hover:bg-white/10 transition-all duration-300">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Background Pattern -->
        <div class="absolute inset-0 z-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <pattern id="pattern" width="10" height="10" patternUnits="userSpaceOnUse">
                    <circle cx="5" cy="5" r="2" fill="currentColor"/>
                </pattern>
                <rect width="100%" height="100%" fill="url(#pattern)"/>
            </svg>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<script>
// Enhanced Particle.js configuration
particlesJS("particles-js", {
    particles: {
        number: { value: 100, density: { enable: true, value_area: 800 } },
        color: { value: "#ffffff" },
        shape: { type: "circle" },
        opacity: { value: 0.5, random: true },
        size: { value: 3, random: true },
        line_linked: {
            enable: true,
            distance: 150,
            color: "#ffffff",
            opacity: 0.2,
            width: 1
        },
        move: {
            enable: true,
            speed: 3,
            direction: "none",
            random: true,
            straight: false,
            out_mode: "out",
            bounce: false
        }
    },
    interactivity: {
        detect_on: "canvas",
        events: {
            onhover: { enable: true, mode: "repulse" },
            onclick: { enable: true, mode: "push" },
            resize: true
        },
        modes: {
            repulse: { distance: 100, duration: 0.4 },
            push: { particles_nb: 4 }
        }
    },
    retina_detect: true
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Enhanced count-up animation
const countUpElements = document.querySelectorAll('.count-up');
countUpElements.forEach(element => {
    const target = parseInt(element.getAttribute('data-count'), 10);
    let count = 0;
    const duration = 2500;
    const increment = target / (duration / 16);

    const updateCount = () => {
        count += increment;
        if (count < target) {
            element.textContent = Math.round(count).toLocaleString();
            requestAnimationFrame(updateCount);
        } else {
            element.textContent = target.toLocaleString();
        }
    };

    // Start animation when element is in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                updateCount();
                observer.unobserve(entry.target);
            }
        });
    });

    observer.observe(element);
});
</script>
