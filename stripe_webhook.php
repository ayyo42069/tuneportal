<?php
require 'config.php';
require 'config/stripe.php';

$payload = file_get_contents('php://input');

// Check if signature header exists
if (!isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
    log_error('Stripe webhook error: Missing signature header', 'ERROR');
    http_response_code(400);
    echo json_encode(['error' => 'Missing signature header']);
    exit();
}

$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
// Replace hardcoded secret with environment variable
$endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET');
if (!$endpoint_secret) {
    log_error('Stripe webhook secret not configured', 'ERROR');
    http_response_code(500);
    exit();
}

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;
        
        // Log the webhook data
        log_error('Stripe webhook received', 'INFO', [
            'session_id' => $session->id,
            'payment_status' => $session->payment_status
        ]);
        // Add before processing the webhook
        $event_id = $event->id;
        $stmt = $conn->prepare("SELECT id FROM processed_webhooks WHERE event_id = ?");
        $stmt->bind_param("s", $event_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            http_response_code(200); // Already processed
            exit();
        }
        
        // After successful processing
        $stmt = $conn->prepare("INSERT INTO processed_webhooks (event_id) VALUES (?)");
        $stmt->bind_param("s", $event_id);
        $stmt->execute();
        $user_id = $session->metadata->user_id;
        $credits = $session->metadata->credits;
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update user credits
            $stmt = $conn->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
            $stmt->bind_param("ii", $credits, $user_id);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Failed to update user credits");
            }
            
            // Add transaction record
            $stmt = $conn->prepare("INSERT INTO credit_transactions (user_id, amount, type, description) VALUES (?, ?, 'purchase', ?)");
            $description = "Purchased " . $credits . " credits";
            $stmt->bind_param("iis", $user_id, $credits, $description);
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Failed to record transaction");
            }
            
            $conn->commit();
            log_error("Credits added successfully", "INFO", [
                'user_id' => $user_id,
                'credits' => $credits,
                'session_id' => $session->id
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            log_error("Database transaction failed", "ERROR", [
                'user_id' => $user_id,
                'credits' => $credits,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch(Exception $e) {
    log_error("Stripe webhook processing failed", "ERROR", [
        'error' => $e->getMessage()
    ]);
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}