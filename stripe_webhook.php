<?php
require 'config.php';
require 'config/stripe.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/stripe_webhook_errors.log');

// Get the raw POST data
$payload = file_get_contents('php://input');

// Check for Stripe signature header
if (!isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
    error_log("Missing Stripe signature header");
    http_response_code(400);
    echo json_encode(['error' => 'Missing signature header']);
    exit();
}

$sig_header = $_SERVER['HTTPS_STRIPE_SIGNATURE'];
$endpoint_secret = 'whsec_pSRrjFsOIDN8Opw9mI4VlGj7FsE85c8d';

error_log("Webhook received with signature: " . $sig_header);
error_log("Webhook payload: " . $payload);

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );

    error_log("Event type: " . $event->type);

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;
        
        // Add credits to user account
        $user_id = $session->metadata->user_id;
        $credits = $session->metadata->credits;
        
        error_log("Processing payment for User ID: $user_id, Credits: $credits");
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update user credits
            $stmt = $conn->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("ii", $credits, $user_id);
            $result = $stmt->execute();
            if (!$result) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            error_log("Credits updated. Affected rows: " . $stmt->affected_rows);
            
            // Add transaction record
            $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'purchase', ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $description = "Purchased " . $credits . " credits";
            $stmt->bind_param("iis", $user_id, $credits, $description);
            $result = $stmt->execute();
            if (!$result) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            error_log("Transaction recorded. Insert ID: " . $stmt->insert_id);
            
            $conn->commit();
            error_log("Transaction committed successfully");
            
            // Update session credits
            $stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $_SESSION['credits'] = $user['credits'];
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch(Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}