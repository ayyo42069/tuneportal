<?php
$isLoggedIn = isset($_SESSION['user_id']);
$footerClass = $isLoggedIn ? 'lg:ml-64' : '';
?>
<!-- footer.php -->
<footer
    class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-white mt-auto z-30 transition-colors duration-300 <?php echo $footerClass; ?>">
    <div class="container mx-auto px-4 <?php echo $isLoggedIn ? 'lg:max-w-[calc(100%-16rem)]' : ''; ?>">
        <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
        <!-- Full footer for index page -->
        <div class="grid md:grid-cols-3 gap-8 py-12">
            <div class="space-y-4">
                <h3 class="text-xl font-bold text-primary">TunePortal</h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">Unlock your vehicle's full potential with
                    professional-grade tuning solutions.</p>
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
                    <li><a href="#features"
                            class="text-gray-600 dark:text-gray-300 hover:text-primary transition-colors block">Features</a>
                    </li>
                    <li><a href="#contact"
                            class="text-gray-600 dark:text-gray-300 hover:text-primary transition-colors block">Contact</a>
                    </li>
                </ul>
            </div>

            <div class="space-y-4">
                <h4 class="font-semibold text-lg">Contact</h4>
                <div class="space-y-2 text-gray-600 dark:text-gray-300">
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
            <div class="border-t border-gray-200 dark:border-gray-700 py-4 text-center">
                <p class="text-gray-600 dark:text-gray-300">
                    &copy; <?= date('Y') ?> TunePortal. All rights reserved.
                </p>
            </div>
        </div>
</footer>

<!-- Add this before closing body tag -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const footer = document.querySelector('footer');
        const sidebar = document.getElementById('sidebar');

        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));

                // Update footer classes for dark mode
                if (footer) {
                    footer.classList.toggle('bg-gray-100');
                    footer.classList.toggle('dark:bg-gray-800');
                    footer.classList.toggle('text-gray-800');
                    footer.classList.toggle('dark:text-white');
                }
            });

            // Check for saved dark mode preference
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');

                // Update footer classes for dark mode
                if (footer) {
                    footer.classList.remove('bg-gray-100');
                    footer.classList.add('dark:bg-gray-800');
                    footer.classList.remove('text-gray-800');
                    footer.classList.add('dark:text-white');
                }
            }
        }

        // Adjust footer position when sidebar toggles
        const sidebarToggle = document.getElementById('sidebar-toggle');
        if (sidebarToggle && sidebar && footer) {
            sidebarToggle.addEventListener('click', () => {
                footer.classList.toggle('lg:ml-64');
            });
        }

        // Ensure footer is in the correct position on page load for larger screens
        function adjustFooterPosition() {
            if (window.innerWidth >= 1024 && sidebar && !sidebar.classList.contains('-translate-x-full')) {
                footer.classList.add('lg:ml-64');
            } else {
                footer.classList.remove('lg:ml-64');
            }
        }

        window.addEventListener('resize', adjustFooterPosition);
        adjustFooterPosition(); // Call on initial load
    });
</script>

</body>
</html>
