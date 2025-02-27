<?php
include 'config.php';
require_auth();

header('Content-Type: application/json');

if (!isset($_GET['model_id'])) {
    echo json_encode(['error' => 'No model ID provided']);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, name, description 
    FROM ecu_types 
    WHERE model_id = ? 
    ORDER BY name
");
$stmt->bind_param("i", $_GET['model_id']);
$stmt->execute();
$result = $stmt->get_result();
$ecus = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($ecus);