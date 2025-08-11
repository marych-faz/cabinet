<?php
$host = 'MySQL-8.4';
$db   = 'cabinet';     // имя твоей БД
$user = 'root';        // пользователь БД
$pass = '';            // пароль (если есть)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}
