<!-- footer.php -->
<footer class="bg-gray-900 text-white mt-auto z-30">
    <div class="container mx-auto px-4">
        <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
            <!-- Full footer for index page -->
            <div class="grid md:grid-cols-3 gap-8 py-12">
                <div class="space-y-4">
                    <h3 class="text-xl font-bold text-primary">TunePortal</h3>
                    <p class="text-gray-400 leading-relaxed">Unlock your vehicle's full potential with professional-grade tuning solutions.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h4 class="font-semibold text-lg">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-primary transition-colors block">Features</a></li>
                        <li><a href="#testimonials" class="text-gray-400 hover:text-primary transition-colors block">Testimonials</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-primary transition-colors block">Contact</a></li>
                    </ul>
                </div>
                
                <div class="space-y-4">
                    <h4 class="font-semibold text-lg">Contact</h4>
                    <div class="space-y-2 text-gray-400">
                        <p class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            support@tuneportal.com
                        </p>
                        <p class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            +1 (555) 123-4567
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Minimal footer for other pages -->
            <div class="py-4">
        <?php endif; ?>
        
        <!-- Copyright section -->
        <div class="border-t border-gray-800 py-4 text-center">
            <p class="text-gray-400">
                &copy; <?= date('Y') ?> TunePortal. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<!-- Add this before closing body tag -->
<script>
    // Improved mobile menu and sidebar toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const header = document.querySelector('header');
        const darkModeToggle = document.getElementById('dark-mode-toggle');

        // Mobile menu toggle
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Sidebar toggle for mobile
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        // Scroll effect for header
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                header.classList.add('shadow-lg');
            } else {
                header.classList.remove('shadow-lg');
            }
        });

        // Close sidebar on window resize if screen becomes larger
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) { // lg breakpoint
                if (sidebar) {
                    sidebar.classList.remove('-translate-x-full');
                }
                if (mobileMenu) {
                    mobileMenu.classList.add('hidden');
                }
            }
        });

        // Dark mode toggle
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
            });

            // Check for saved dark mode preference
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            }
        }

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
    });
</script>
</body>
</html>

