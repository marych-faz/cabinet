<?php

// Конфигурация подключения к БД
$host = 'MySQL-8.4';
$dbname = 'cabinet';
$username = 'root';
$password = '';

try {
    // Подключение через PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Подключение к базе данных установлено.\n";

    // Получаем всех пользователей, у которых пароль ещё не захэширован
    $stmt = $pdo->query("SELECT id, pass FROM users WHERE is_hashed = 0");

    if ($stmt->rowCount() === 0) {
        echo "ℹ️ Нет незахэшированных паролей. Завершаем работу.\n";
        exit;
    }

    echo "🔄 Начинаем хэшировать пароли...\n";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userId = $row['id'];
        $plainPassword = $row['pass'];

        // Создаём хэш
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Обновляем запись в БД
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET pass = ?, is_hashed = 1 
            WHERE id = ?
        ");

        $updateStmt->execute([$hashedPassword, $userId]);

        echo "🔐 Пароль пользователя ID={$userId} успешно обновлён.\n";
    }

    echo "✅ Все пароли были захэшированы!\n";

} catch (PDOException $e) {
    die("❌ Ошибка подключения к базе данных: " . $e->getMessage());
}