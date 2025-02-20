<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Set your Stripe secret key
\Stripe\Stripe::setApiKey('sk_test_51Qubl2PP22uFyni1lqPVNCrJJQ70T8ngBg7Opz20gprZIqH0qEtCrAeluuRXPZ7D8kspWWRNjIjAyEvmIHtpT31m00g8VKFwwu');

// Set your publishable key (for frontend)
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51Qubl2PP22uFyni1AlTeVk45DOO0BBflaMYF5tIOjKWIB5SvNae0Qog0LmMqIHPtb2HB6tOyEqfgwsAxgACzNbxN00Hx51gWqN');

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