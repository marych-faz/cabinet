<?php
session_start();

// Простая проверка (в реальности используйте БД и хеширование!)
$valid_username = 'admin';
$valid_password = '12345'; // Всё в открытом виде — только для примера!

if ($_POST['username'] === $valid_username && $_POST['password'] === $valid_password) {
    $_SESSION['user'] = $valid_username;
    header('Location: dashboard.php');
    exit;
} else {
    $_SESSION['error'] = 'Неверный логин или пароль';
    header('Location: index.php');
    exit;
}
?>