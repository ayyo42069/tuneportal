<?php
include 'config.php';
require_auth();

header('Content-Type: application/json');

if (!isset($_GET['manufacturer_id'])) {
    echo json_encode(['error' => 'No manufacturer ID provided']);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, name, year_start, year_end 
    FROM car_models 
    WHERE manufacturer_id = ? 
    ORDER BY name
");
$stmt->bind_param("i", $_GET['manufacturer_id']);
$stmt->execute();
$result = $stmt->get_result();
$models = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($models);