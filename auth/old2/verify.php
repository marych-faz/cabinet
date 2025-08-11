<?php
session_start();

if (!isset($_SESSION['captcha'])) {
    die(json_encode(['success' => false, 'error' => 'CAPTCHA не инициализирована']));
}

$userInput = strtoupper($_POST['captcha'] ?? '');
$captcha = strtoupper($_SESSION['captcha']);

if ($userInput !== $captcha) {
    die(json_encode(['success' => false, 'error' => 'Неверная CAPTCHA']));
}

unset($_SESSION['captcha']);
echo json_encode(['success' => true]);
?>