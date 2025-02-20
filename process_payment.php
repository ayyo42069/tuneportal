<?php
include 'config.php';
require 'config/stripe.php';
require_auth();

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Check if package_id exists and is valid
    if (!isset($data['package_id']) || !isset(CREDIT_PACKAGES[$data['package_id']])) {
        throw new Exception('Invalid package selected');
    }

    $package = CREDIT_PACKAGES[$data['package_id']];
    
    // Create Stripe Checkout Session
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => $package['currency'],
                'unit_amount' => $package['price'] * 100, // Convert to cents
                'product_data' => [
                    'name' => $package['description'],
                    'description' => 'Credits for TunePortal services',
                ],
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'https://tuneportal.germanywestcentral.cloudapp.azure.com/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://tuneportal.germanywestcentral.cloudapp.azure.com/credits.php',
        'metadata' => [
            'user_id' => $_SESSION['user_id'],
            'credits' => $package['credits']
        ],
    ]);

    echo json_encode(['id' => $checkout_session->id]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}