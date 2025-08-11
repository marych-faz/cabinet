<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $periodStart = $_POST['periodStart'] ?? null;
    $periodEnd = $_POST['periodEnd'] ?? null;
    
    if ($periodStart && $periodEnd) {
        $_SESSION['PERIOD_START'] = $periodStart;
        $_SESSION['PERIOD_END'] = $periodEnd;
        $response['success'] = true;
    } else {
        $response['error'] = 'Не указаны даты периода';
    }
} else {
    $response['error'] = 'Неподдерживаемый метод запроса';
}

echo json_encode($response);
?>