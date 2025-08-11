<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $periodStart = $_POST['periodStart'] ?? null;
    $periodEnd = $_POST['periodEnd'] ?? null;
    
    if ($periodStart && $periodEnd) {
        $_SESSION['PERIOD_START'] = $periodStart;
        $_SESSION['PERIOD_END'] = $periodEnd;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Не указаны даты периода']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Неподдерживаемый метод запроса']);
}
?>