<?php
require_once __DIR__ . '/../includes/db_connect.php';

$login = $_GET['login'] ?? '';

$stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
$stmt->execute([$login]);

header('Content-Type: application/json');
echo json_encode(['available' => !$stmt->fetch()]);
?>