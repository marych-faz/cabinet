<?php
// Настройки подключения к БД
$host = 'MySQL-8.4';     // обычно localhost
$dbname = 'cabinet';     // имя вашей базы данных
$username = 'root';      // логин пользователя БД (может быть другим)
$password = '';          // пароль (если есть)

try {
    // Создаем подключение через PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Устанавливаем режим вывода ошибок SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL-запрос для выборки всех пользователей
    $sql = "SELECT name FROM users";
    $stmt = $pdo->query($sql);

    // Получаем все строки
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($users) {
        echo "<h2>Список пользователей:</h2>";
        echo "<ul>";
        foreach ($users as $row) {
            echo "<li>" . htmlspecialchars($row['name']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "Пользователи не найдены.";
    }

} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}