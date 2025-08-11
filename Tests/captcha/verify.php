<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = $_POST['captcha'];
    $name = htmlspecialchars($_POST['name']);

    if (!isset($_SESSION['captcha'])) {
        die("Ошибка: Капча не была сгенерирована.");
    }

    if (strtoupper($user_input) === strtoupper($_SESSION['captcha'])) {
        echo "✅ Привет, $name! Вы успешно прошли проверку.";
        
        // Здесь можно сохранить данные в БД (MySQL)
        // Подключение к БД через mysqli или PDO
        // ...

    } else {
        echo "❌ Неверный код. Попробуйте ещё раз.";
        echo '<br><a href="index.html">Вернуться</a>';
    }
} else {
    header("Location: index.html");
}
