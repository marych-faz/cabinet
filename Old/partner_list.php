<?php
// Настройки подключения к базе данных
$host = 'MySQL-8.4';
$dbname = 'cabinet';
$username = 'root';
$password = '';

try {
    // Подключение к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем параметр фильтра из GET-запроса
    $showArchived = isset($_GET['show_archived']);

    // Формируем SQL запрос в зависимости от фильтра
    $sql = "SELECT * FROM users WHERE is_admin = 0";
    if (!$showArchived) {
        $sql .= " AND is_archived = 0";
    }

    // Выполняем запрос
    $stmt = $pdo->query($sql);
    $partners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Подключаем HTML-шаблон
    include 'partner_list.html';

} catch (PDOException $e) {
    // Обработка ошибки подключения
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
