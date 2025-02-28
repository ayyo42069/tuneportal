<?php include 'header.php'; ?>

<div class="min-h-screen bg-gradient-hero py-12 px-4 sm:px-6 lg:px-8 mt-16">
    <div class="max-w-4xl mx-auto h-[calc(100vh-12rem)]">
    <div class="glass-card animate-fade-in-up h-full flex flex-col">
    <h1 class="text-3xl font-bold text-gradient mb-8 sticky top-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl py-4 z-10">Terms and Conditions</h1>
            
    <div class="space-y-6 text-gray-700 dark:text-gray-300 overflow-y-auto custom-scrollbar flex-1 pr-4">
                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-gradient">1. Acceptance of Terms</h2>
                    <p>By accessing and using Tuning Portal, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, please do not use our services.</p>
                </section>

                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-gradient">2. Service Description</h2>
                    <p>Tuning Portal provides automotive tuning file services. We offer:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>ECU file modifications</li>
                        <li>Performance tuning services</li>
                        <li>File analysis and optimization</li>
                        <li>Technical support related to our services</li>
                    </ul>
                </section>

                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-gradient">3. User Responsibilities</h2>
                    <p>Users must:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Provide accurate and complete information</li>
                        <li>Maintain the security of their account</li>
                        <li>Use the service in compliance with local laws</li>
                        <li>Not attempt to reverse engineer our services</li>
                    </ul>
                </section>

                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-gradient">4. Payment and Credits</h2>
                    <p>Our service operates on a credit-based system:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Credits must be purchased before using services</li>
                        <li>Credit purchases are final and non-refundable</li>
                        <li>Prices are subject to change without notice</li>
                        <li>All transactions are processed securely</li>
                    </ul>
                </section>

                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-gradient">5. Intellectual Property</h2>
                    <p>All content and modifications provided through our service remain our intellectual property. Users receive a license to use modified files for their personal use only.</p>
                </section>

                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-gradient">6. Limitation of Liability</h2>
                    <p>Tuning Portal is not liable for:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Vehicle damage resulting from modifications</li>
                        <li>Loss of warranty coverage</li>
                        <li>Performance variations</li>
                        <li>Indirect or consequential damages</li>
                    </ul>
                </section>

                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-gradient">7. Privacy Policy</h2>
                    <p>We protect your privacy and handle personal data in accordance with our Privacy Policy. By using our services, you consent to our data practices.</p>
                </section>

                <section class="space-y-4">
                    <h2 class="text-xl font-semibold text-gradient">8. Modifications to Terms</h2>
                    <p>We reserve the right to modify these terms at any time. Continued use of the service after changes constitutes acceptance of new terms.</p>
                </section>

                <div class="mt-8 p-4 glass-feature rounded-xl">
                    <p class="text-sm">Last updated: <?= date('F j, Y') ?></p>
                    <p class="text-sm mt-2">For questions about these terms, please contact our support team.</p>
                </div>
            </div>

            <div class="mt-8 flex justify-end sticky bottom-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl py-4">
                <a href="register.php" class="glass-button-primary px-6 py-2 rounded-xl">
                    Return to Registration
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>