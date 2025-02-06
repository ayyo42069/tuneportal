<?php include 'header.php'; ?>

<main class="flex-grow mt-16">
    <!-- Hero Section with Video Background -->
    <section class="relative h-screen bg-gradient-to-r from-red-600 to-red-800 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <video autoplay loop muted class="w-full h-full object-cover opacity-50">
                <source src="path/to/your/car-tuning-video.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <div class="relative z-10 h-full flex items-center">
            <div class="container mx-auto px-4 text-center text-white">
                <h1 class="text-5xl md:text-7xl font-bold mb-6 animate-fade-in-up">
                    Unleash Your Car's<br><span class="text-yellow-400">True Potential</span>
                </h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto opacity-90 animate-fade-in-up animation-delay-300">
                    Experience the pinnacle of automotive performance with our cutting-edge ECU tuning solutions
                </p>
                <a href="register.php" 
                   class="inline-block bg-white text-red-600 px-8 py-3 rounded-full text-lg font-semibold 
                          hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 animate-fade-in-up animation-delay-600">
                    Start Tuning Now
                </a>
            </div>
        </div>
    </section>

    <!-- Dynamic Feature Showcase -->
    <section class="py-20 bg-gray-100" x-data="{ currentFeature: 0 }">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800">Why Choose TunePortal?</h2>
            
            <div class="relative overflow-hidden">
                <div class="flex transition-transform duration-500 ease-in-out" :style="{ transform: `translateX(-${currentFeature * 100}%)` }">
                    <!-- Feature 1 -->
                    <div class="w-full flex-shrink-0 flex flex-col md:flex-row items-center justify-between">
                        <div class="md:w-1/2 mb-8 md:mb-0">
                            <h3 class="text-3xl font-bold mb-4 text-red-600">Advanced ECU Tuning</h3>
                            <p class="text-xl text-gray-600">Precision-engineered performance upgrades for maximum power and efficiency</p>
                        </div>
                        <div class="md:w-1/2">
                            <img src="path/to/ecu-tuning-image.jpg" alt="Advanced ECU Tuning" class="rounded-lg shadow-xl">
                        </div>
                    </div>
                    <!-- Feature 2 -->
                    <div class="w-full flex-shrink-0 flex flex-col md:flex-row items-center justify-between">
                        <div class="md:w-1/2 mb-8 md:mb-0">
                            <h3 class="text-3xl font-bold mb-4 text-red-600">Real-time Diagnostics</h3>
                            <p class="text-xl text-gray-600">Instant performance feedback and diagnostics for optimal tuning results</p>
                        </div>
                        <div class="md:w-1/2">
                            <img src="path/to/diagnostics-image.jpg" alt="Real-time Diagnostics" class="rounded-lg shadow-xl">
                        </div>
                    </div>
                    <!-- Feature 3 -->
                    <div class="w-full flex-shrink-0 flex flex-col md:flex-row items-center justify-between">
                        <div class="md:w-1/2 mb-8 md:mb-0">
                            <h3 class="text-3xl font-bold mb-4 text-red-600">Custom Mapping</h3>
                            <p class="text-xl text-gray-600">Tailor-made performance maps for your specific vehicle and driving style</p>
                        </div>
                        <div class="md:w-1/2">
                            <img src="path/to/custom-mapping-image.jpg" alt="Custom Mapping" class="rounded-lg shadow-xl">
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                <button @click="currentFeature = (currentFeature - 1 + 3) % 3" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white p-2 rounded-full shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button @click="currentFeature = (currentFeature + 1) % 3" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white p-2 rounded-full shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Performance Stats Section -->
    <section class="py-20 bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16">Unleash the Power</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="text-5xl font-bold text-red-500 mb-2">+25%</div>
                    <p class="text-xl">Horsepower Gain</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold text-red-500 mb-2">+30%</div>
                    <p class="text-xl">Torque Increase</p>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold text-red-500 mb-2">-15%</div>
                    <p class="text-xl">Fuel Consumption</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-red-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-6">Start Your Tuning Journey Today</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Join the ranks of satisfied tuners and unleash your vehicle's true potential
            </p>
            <div class="flex justify-center space-x-4">
                <a href="register.php" 
                   class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold 
                          hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                    Create Free Account
                </a>
                <a href="#features" 
                   class="border border-white hover:bg-white hover:text-red-600 px-8 py-3 rounded-lg font-semibold transition-colors">
                    Learn More
                </a>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    // Auto-advance the feature showcase
    setInterval(() => {
        const showcase = document.querySelector('[x-data]').__x.$data;
        showcase.currentFeature = (showcase.currentFeature + 1) % 3;
    }, 5000);

    // Animate count-up for performance stats
    const countUpElements = document.querySelectorAll('.text-5xl.font-bold.text-red-500');
    countUpElements.forEach(element => {
        const target = parseInt(element.textContent.replace(/[^0-9-]/g, ''), 10);
        let count = 0;
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60 FPS

        const updateCount = () => {
            count += increment;
            if (Math.abs(count) < Math.abs(target)) {
                element.textContent = `${count > 0 ? '+' : ''}${Math.round(count)}%`;
                requestAnimationFrame(updateCount);
            } else {
                element.textContent = `${target > 0 ? '+' : ''}${target}%`;
            }
        };

        updateCount();
    });
</script>