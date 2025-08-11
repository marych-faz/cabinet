<?php
session_start();

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['periodStart']) && isset($_POST['periodEnd'])) {
    $_SESSION['PERIOD_START'] = $_POST['periodStart'];
    $_SESSION['PERIOD_END'] = $_POST['periodEnd'];
    $response['success'] = true;
}

header('Content-Type: application/json');
echo json_encode($response);
?>