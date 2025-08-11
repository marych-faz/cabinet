<?php
require_once __DIR__ . '/../includes/db_connect.php';

$bik = $_GET['bik'] ?? '';

$stmt = $pdo->prepare("SELECT name, ks FROM banks WHERE bic = ?");
$stmt->execute([$bik]);
$bank = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'success' => $bank !== false,
    'bank' => $bank ?: null
]);
?>