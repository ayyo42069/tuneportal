<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

// Set your Stripe secret key
\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

// Set your publishable key (for frontend)
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY'));

// Define credit package options
define('CREDIT_PACKAGES', [
    [
        'credits' => 100,
        'price' => 10.00,
        'currency' => 'USD',
        'description' => '100 Credits'
    ],
    [
        'credits' => 500,
        'price' => 45.00,
        'currency' => 'USD',
        'description' => '500 Credits (10% off)'
    ],
    [
        'credits' => 1000,
        'price' => 80.00,
        'currency' => 'USD',
        'description' => '1000 Credits (20% off)'
    ]
]);