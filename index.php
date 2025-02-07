<?php include 'header.php'; ?>

<!-- Splash Screen -->
<div id="splash-screen" class="fixed inset-0 z-[60] flex flex-col items-center justify-center bg-gray-900 transition-opacity duration-500">
    <div class="relative w-48 h-48 mb-8">
        <img src="path_to_your_logo.png" alt="TunePortal" class="object-contain w-full h-full">
    </div>

    <!-- Matrix-style loading text -->
    <div id="matrix-text" class="font-mono text-white mb-4 h-6"></div>

    <!-- Progress bar container -->
    <div class="w-64 h-1 bg-gray-700 rounded-full overflow-hidden">
        <div id="progress-bar" class="h-full bg-white transition-all duration-100 ease-out" style="width: 0%"></div>
    </div>

    <!-- Progress percentage -->
    <div id="progress-percentage" class="mt-2 font-mono text-sm text-white">0%</div>
</div>

<!-- Rest of your index.php content -->
<div id="main-content" class="hidden">
    <!-- Hero Section with Video Background -->
    <section class="relative h-screen flex items-center justify-center overflow-hidden">
        <video autoplay loop muted class="absolute w-full h-full object-cover">
            <source src="path_to_your_sports_car_video.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black opacity-60"></div>
        <div class="relative z-10 text-center text-white px-4">
            <h1 class="text-5xl md:text-7xl font-bold mb-4 animate-fade-in-up">Unleash Your Car's Potential</h1>
            <p class="text-xl md:text-2xl mb-8 animate-fade-in-up animate-delay-100">Experience the thrill of optimized performance with TunePortal</p>
            <a href="#get-started" class="bg-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-red-600 transition duration-300 animate-fade-in-up animate-delay-200 inline-block">
                Start Tuning Now
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Why Choose TunePortal?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg transform hover:scale-105 transition duration-300">
                    <div class="text-primary text-4xl mb-4"><i class="fas fa-tachometer-alt"></i></div>
                    <h3 class="text-xl font-semibold mb-2">Precision Tuning</h3>
                    <p>Our advanced algorithms ensure your car performs at its absolute best, tailored to your specific needs.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg transform hover:scale-105 transition duration-300">
                    <div class="text-primary text-4xl mb-4"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="text-xl font-semibold mb-2">Safety First</h3>
                    <p>We prioritize your vehicle's longevity, ensuring performance gains without compromising reliability.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg transform hover:scale-105 transition duration-300">
                    <div class="text-primary text-4xl mb-4"><i class="fas fa-users"></i></div>
                    <h3 class="text-xl font-semibold mb-2">Expert Support</h3>
                    <p>Our team of experienced tuners is always ready to assist you with personalized solutions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">How It Works</h2>
            <div class="flex flex-col md:flex-row items-center justify-center space-y-8 md:space-y-0 md:space-x-8">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mb-4 mx-auto">1</div>
                    <h3 class="text-xl font-semibold mb-2">Select Your Vehicle</h3>
                    <p>Choose your car make, model, and year from our extensive database.</p>
                </div>
                <div class="text-center">
                    <div class="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mb-4 mx-auto">2</div>
                    <h3 class="text-xl font-semibold mb-2">Customize Your Tune</h3>
                    <p>Pick your desired performance upgrades and tuning options.</p>
                </div>
                <div class="text-center">
                    <div class="bg-primary text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mb-4 mx-auto">3</div>
                    <h3 class="text-xl font-semibold mb-2">Receive Your Tune</h3>
                    <p>Download your custom tune file and flash it to your ECU.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">What Our Customers Say</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <img src="path_to_customer_image1.jpg" alt="Customer" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold">John Doe</h4>
                            <p class="text-gray-600">BMW M3 Owner</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"TunePortal transformed my M3. The power gains are incredible, and the throttle response is now razor-sharp!"</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <img src="path_to_customer_image2.jpg" alt="Customer" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold">Jane Smith</h4>
                            <p class="text-gray-600">Audi RS6 Owner</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"I'm amazed at how much untapped potential my RS6 had. TunePortal unleashed a whole new level of performance!"</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <img src="path_to_customer_image3.jpg" alt="Customer" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold">Mike Johnson</h4>
                            <p class="text-gray-600">Ford Mustang GT Owner</p>
                        </div>
                    </div>
                    <p class="text-gray-700">"The customer support at TunePortal is unmatched. They helped me every step of the way to achieve the perfect tune for my Mustang."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="get-started" class="py-20 bg-primary text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Ready to Elevate Your Ride?</h2>
            <p class="text-xl mb-8">Join thousands of satisfied customers and experience the TunePortal difference today!</p>
            <a href="register.php" class="bg-white text-primary px-8 py-4 rounded-full text-lg font-semibold hover:bg-gray-100 transition duration-300 inline-block">
                Get Your Free Tune Estimate
            </a>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Frequently Asked Questions</h2>
            <div class="max-w-3xl mx-auto space-y-6">
                <div class="bg-gray-100 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-2">Is tuning safe for my car?</h3>
                    <p>Yes, when done properly. Our tunes are developed by experts to maximize performance while maintaining reliability.</p>
                </div>
                <div class="bg-gray-100 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-2">Will tuning void my warranty?</h3>
                    <p>It depends on your manufacturer. We offer warranty-friendly tunes and can advise on the best options for your situation.</p>
                </div>
                <div class="bg-gray-100 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-2">How much horsepower can I gain?</h3>
                    <p>Gains vary by vehicle, but many customers see 10-15% increases in horsepower and torque.</p>
                </div>
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const splashScreen = document.getElementById('splash-screen');
    const mainContent = document.getElementById('main-content');
    const progressBar = document.getElementById('progress-bar');
    const progressPercentage = document.getElementById('progress-percentage');
    const matrixText = document.getElementById('matrix-text');

    let progress = 0;
    const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$#@%";

    // Matrix text effect
    const matrixInterval = setInterval(() => {
        const randomText = Array(8).fill(0).map(() => characters.charAt(Math.floor(Math.random() * characters.length))).join("");
        matrixText.textContent = `LOADING_SYSTEM: ${randomText}`;
    }, 50);

    // Progress bar animation
    const interval = setInterval(() => {
        progress += 1;
        progressBar.style.width = `${progress}%`;
        progressPercentage.textContent = `${progress}%`;

        if (progress >= 100) {
            clearInterval(interval);
            clearInterval(matrixInterval);
            setTimeout(() => {
                splashScreen.classList.add('opacity-0', 'pointer-events-none');
                mainContent.classList.remove('hidden');
            }, 500);
        }
    }, 30);
});
</script>

<script>
// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Animate elements when they come into view
const animateOnScroll = (entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in-up');
            observer.unobserve(entry.target);
        }
    });
};

const observer = new IntersectionObserver(animateOnScroll, {
    root: null,
    threshold: 0.1
});

document.querySelectorAll('section > div > *').forEach(el => {
    observer.observe(el);
});
</script>

<?php include 'footer.php'; ?>

