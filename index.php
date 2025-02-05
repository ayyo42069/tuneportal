<?php include 'header.php'; ?>

<main class="flex-grow mt-16">
    <!-- Hero Section with Particle.js Background -->
    <section class="relative h-screen bg-gradient-to-r from-primary to-secondary overflow-hidden">
        <div id="particles-js" class="absolute inset-0 z-0"></div>
        
        <div class="relative z-10 h-full flex items-center">
            <div class="container mx-auto px-4 text-center text-white">
                <div class="live-stats absolute top-4 right-4 glassmorphism p-4 rounded-lg">
                    <div class="flex space-x-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold count-up" data-count="2543">0</div>
                            <div class="text-sm">Tuned Vehicles</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold count-up" data-count="178">0</div>
                            <div class="text-sm">Expert Tuners</div>
                        </div>
                    </div>
                </div>
                
                <h1 class="text-6xl md:text-7xl font-bold mb-6 animate-fade-in-up">
                    Unleash Your Car's<br><span class="text-secondary dark:text-primary">True Potential</span>
                </h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto opacity-90 animate-fade-in-up animation-delay-300">
                    Experience the pinnacle of automotive performance with our cutting-edge ECU tuning solutions
                </p>
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <a href="register.php" 
                       class="inline-block bg-white text-primary px-8 py-3 rounded-full text-lg font-semibold 
                              hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 animate-fade-in-up animation-delay-600">
                        Start Tuning Now
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Animated Car Silhouette -->
        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-4xl">
            <svg class="w-full" viewBox="0 0 1200 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 250h1200" stroke="rgba(255,255,255,0.2)" stroke-width="2"/>
                <path class="car-body" d="M100 200c0-66.27 53.73-120 120-120h760c66.27 0 120 53.73 120 120v50H100v-50z" fill="rgba(255,255,255,0.1)"/>
                <circle class="wheel" cx="300" cy="250" r="50" fill="rgba(255,255,255,0.2)"/>
                <circle class="wheel" cx="900" cy="250" r="50" fill="rgba(255,255,255,0.2)"/>
            </svg>
        </div>
    </section>

    <!-- Features Grid with Hover Effects -->
    <section class="py-20 bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800 dark:text-white">Why Choose TunePortal?</h2>
            
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Feature 1 -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800 dark:text-white">Advanced ECU Tuning</h3>
                    <p class="text-gray-600 dark:text-gray-300">Precision-engineered performance upgrades for maximum power and efficiency</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-secondary bg-opacity-10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800 dark:text-white">Real-time Diagnostics</h3>
                    <p class="text-gray-600 dark:text-gray-300">Instant performance feedback and diagnostics for optimal tuning results</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h3m-3 4h3m-6 4h3M9 7h3m-3 4h3m-6 4h3M6 17v-4m9 4V7m3 10v-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800 dark:text-white">Custom Mapping</h3>
                    <p class="text-gray-600 dark:text-gray-300">Tailor-made performance maps for your specific vehicle and driving style</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Performance Graph -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800 dark:text-white">Visualize Your Performance Gains</h2>
            <div class="max-w-4xl mx-auto bg-gray-100 dark:bg-gray-700 p-6 rounded-xl shadow-lg">
                <canvas id="performanceGraph" width="400" height="200"></canvas>
            </div>
        </div>
    </section>

    <!-- Testimonials Carousel -->
    <section class="py-20 bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800 dark:text-white">What Our Customers Say</h2>
            <div class="max-w-4xl mx-auto">
                <div class="testimonial-carousel">
                    <!-- Testimonial 1 -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg mb-8">
                        <p class="text-gray-600 dark:text-gray-300 mb-4">"TunePortal transformed my car's performance. The power gains are incredible, and the fuel efficiency improvement is a welcome bonus!"</p>
                        <div class="flex items-center">
                            <img src="https://i.pravatar.cc/60?img=1" alt="Customer" class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">John Doe</h4>
                                <p class="text-gray-500 dark:text-gray-400">BMW M3 Owner</p>
                            </div>
                        </div>
                    </div>
                    <!-- Testimonial 2 -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg mb-8">
                        <p class="text-gray-600 dark:text-gray-300 mb-4">"The real-time diagnostics feature is a game-changer. I can fine-tune my car's performance on the fly!"</p>
                        <div class="flex items-center">
                            <img src="https://i.pravatar.cc/60?img=2" alt="Customer" class="w-12 h-12 rounded-full mr-4">
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Jane Smith</h4>
                                <p class="text-gray-500 dark:text-gray-400">Audi RS6 Enthusiast</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section with Animated Background -->
    <section class="relative py-20 bg-primary text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <svg class="w-full h-full" viewBox="0 0 1200 600" xmlns="http://www.w3.org/2000/svg">
                <path fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2" d="M0,600 C600,200 900,100 1200,600" />
                <path fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2" d="M0,450 C300,50 900,50 1200,450" />
            </svg>
        </div>
        <div class="container mx-auto px-4 relative z-10">
            <h2 class="text-4xl font-bold mb-6 text-center">Start Your Tuning Journey Today</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto text-center opacity-90">
                Join the ranks of satisfied tuners and unleash your vehicle's true potential
            </p>
            <div class="flex justify-center space-x-4">
                <a href="register.php" 
                   class="bg-white text-primary px-8 py-3 rounded-lg font-semibold 
                          hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                    Create Free Account
                </a>
                <a href="#features" 
                   class="border border-white hover:bg-white hover:text-primary px-8 py-3 rounded-lg font-semibold transition-colors">
                    Learn More
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    // Animated car silhouette
    const car = document.querySelector('.car-body');
    const wheels = document.querySelectorAll('.wheel');
    
    function animateCar() {
        car.animate([
            { transform: 'translateY(0px)' },
            { transform: 'translateY(-10px)' },
            { transform: 'translateY(0px)' }
        ], {
            duration: 1000,
            iterations: Infinity
        });

        wheels.forEach(wheel => {
            wheel.animate([
                { transform: 'rotate(0deg)' },
                { transform: 'rotate(360deg)' }
            ], {
                duration: 1000,
                iterations: Infinity
            });
        });
    }

    animateCar();

    // Performance Graph
    const ctx = document.getElementById('performanceGraph').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Stock', 'Stage 1', 'Stage 2', 'Stage 3'],
            datasets: [{
                label: 'Horsepower',
                data: [200, 250, 300, 350],
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }, {
                label: 'Torque',
                data: [180, 220, 260, 300],
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>

