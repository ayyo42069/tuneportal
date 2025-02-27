<?php
include 'config.php';
require_auth(true);

if (!isset($_GET['id'])) die("Invalid request");

$tool_id = (int)$_GET['id'];
$tool = $conn->query("SELECT * FROM tools WHERE id = $tool_id")->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($tool);