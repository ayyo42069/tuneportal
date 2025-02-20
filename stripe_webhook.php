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
            
            // Add transaction record - fixed to match database structure
            $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, ?, ?)");
            $description = "Purchased " . $credits . " credits";
            $type = "purchase";
            $stmt->bind_param("iiss", $user_id, $credits, $type, $description);
            $stmt->execute();
            
            $conn->commit();
            
            // Log success
            error_log("Credits added successfully: User ID: $user_id, Credits: $credits");
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Error in webhook: " . $e->getMessage());
            throw $e;
        }
    }

    http_response_code(200);
} catch(Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(400);
    exit();
}