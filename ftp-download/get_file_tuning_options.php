<?php
include 'config.php';
require_auth(true);

if (!isset($_GET['file_id'])) {
    die(json_encode(['error' => 'No file ID provided']));
}

$file_id = (int)$_GET['file_id'];

$stmt = $conn->prepare("
    SELECT to.name
    FROM file_tuning_options fto
    JOIN tuning_options `to` ON fto.option_id = to.id
    WHERE fto.file_id = ?
");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();

$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = $row['name'];
}

header('Content-Type: application/json');
echo json_encode(['options' => $options]);