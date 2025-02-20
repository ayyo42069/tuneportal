<?php
require 'config.php';
require 'config/stripe.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/tuneportal/stripe_webhook.log');

$payload = file_get_contents('php://input');

// Check if signature header exists
if (!isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
    error_log('Stripe webhook error: Missing signature header');
    http_response_code(400);
    echo json_encode(['error' => 'Missing signature header']);
    exit();
}

$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$endpoint_secret = 'whsec_pSRrjFsOIDN8Opw9mI4VlGj7FsE85c8d';

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;
        
        // Log the webhook data
        error_log('Webhook received: ' . json_encode($session));
        
        // Add credits to user account
        $user_id = $session->metadata->user_id;
        $credits = $session->metadata->credits;
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update user credits
            $stmt = $conn->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
            $stmt->bind_param("ii", $credits, $user_id);
            $stmt->execute();
            
            // Add transaction record
            $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'purchase', ?)");
            $description = "Purchased " . $credits . " credits";
            $stmt->bind_param("iis", $user_id, $credits, $description);
            $stmt->execute();
            
            $conn->commit();
            error_log("Credits added successfully: User ID: $user_id, Credits: $credits");
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch(Exception $e) {
    error_log("Stripe webhook error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}