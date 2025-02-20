<?php
require 'config.php';
require 'config/stripe.php';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$endpoint_secret = 'whsec_pSRrjFsOIDN8Opw9mI4VlGj7FsE85c8d';

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;
        
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
            $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, description, payment_id) VALUES (?, ?, ?, ?)");
            $description = "Purchased " . $credits . " credits";
            $stmt->bind_param("iiss", $user_id, $credits, $description, $session->payment_intent);
            $stmt->execute();
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    http_response_code(200);
} catch(Exception $e) {
    http_response_code(400);
    exit();
}