<?php include 'header.php'; ?>

<main class="flex-grow mt-16">
    <!-- Hero Section with Image Background -->
    <section class="relative h-screen bg-cover bg-center" style="background-image: url('images/hero-bg.jpg');">
        <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
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
                    Unlock 30% More Horsepower
                </h1>
                <p class="text-xl mb-8 max-w-2xl mx-auto opacity-90 animate-fade-in-up animation-delay-300">
                    Our ECU tuning solutions deliver dyno-proven performance gains.
                </p>
                <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php"
                    class="inline-block bg-white text-primary px-8 py-3 rounded-full text-lg font-semibold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 animate-fade-in-up animation-delay-600">
                    Get Your Free Quote
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Grid with Hover Effects -->
    <section class="py-20 bg-gray-50 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800 dark:text-white">Why Choose TunePortal?
            </h2>
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Feature 1 -->
                <div
                    class="p-6 bg-white dark:bg-gray-800 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-2">
                    <div
                        class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800 dark:text-white">Experience Unmatched Power
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">Unlock your engine's hidden potential with our
                        precision-engineered tuning.</p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="p-6 bg-white dark:bg-gray-800 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-2">
                    <div
                        class="w-16 h-16 bg-secondary bg-opacity-10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800 dark:text-white">Tune with Confidence</h3>
                    <p class="text-gray-600 dark:text-gray-300">Get instant feedback and diagnostics to ensure
                        optimal and safe tuning results.</p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="p-6 bg-white dark:bg-gray-800 rounded-xl hover:shadow-lg transition-all duration-300 transform hover:-translate-y-2">
                    <div
                        class="w-16 h-16 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h3m-3 4h3m-6 4h3M9 7h3m-3 4h3m-6 4h3M6 17v-4m9 4V7m3 10v-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-3 text-gray-800 dark:text-white">Personalized Performance
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">We create custom maps tailored to your specific
                        vehicle and driving preferences.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section class="py-20 bg-gray-100 dark:bg-gray-700">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-16 text-gray-800 dark:text-white">What Our Customers Say
            </h2>
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md">
                    <p class="text-gray-700 dark:text-gray-300 italic mb-4">"TunePortal transformed my car! I
                        gained noticeable horsepower and the throttle response is amazing. Highly recommend!"</p>
                    <div class="flex items-center">
                        <img src="images/testimonial-1.jpg" alt="John Doe" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">John Doe</p>
                            <p class="text-sm text-gray-500">Car Enthusiast</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md">
                    <p class="text-gray-700 dark:text-gray-300 italic mb-4">"The real-time diagnostics are a
                        game-changer. I was able to fine-tune my car for optimal performance. Great product!"</p>
                    <div class="flex items-center">
                        <img src="images/testimonial-2.jpg" alt="Jane Smith" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">Jane Smith</p>
                            <p class="text-sm text-gray-500">Professional Driver</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-20 bg-primary text-white overflow-hidden">
        <div class="absolute inset-0 z-0">
            <svg class="w-full h-full" viewBox="0 0 1200 600" xmlns="http://www.w3.org/2000/svg">
                <path fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"
                    d="M0,600 C600,200 900,100 1200,600" />
                <path fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"
                    d="M0,450 C300,50 900,50 1200,450" />
            </svg>
        </div>
        <div class="container mx-auto px-4 relative z-10">
            <h2 class="text-4xl font-bold mb-6 text-center">Get a Free Performance Estimate</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto text-center opacity-90">
                Find out how much power you can unlock with our expert ECU tuning.
            </p>
            <div class="flex justify-center space-x-4">
                <a href="register.php"
                    class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                    Unlock Your Car's Potential
                </a>
                <a href="#features"
                    class="border border-white hover:bg-white hover:text-primary px-8 py-3 rounded-lg font-semibold transition-colors">
                    Learn More
                </a>
            </div>
        </div>
    </section>
</main>

<script>
    // Animate count-up
    const countUpElements = document.querySelectorAll('.count-up');

    countUpElements.forEach(element => {
        const target = parseInt(element.getAttribute('data-count'), 10);
        const countUp = new CountUp(element, 0, target, 0, 2.5); // Adjust options as needed

        if (!countUp.error) {
            countUp.start();
        } else {
            console.error(countUp.error);
        }
    });
</script>

<?php include 'footer.php'; ?>
