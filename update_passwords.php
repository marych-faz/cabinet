<?php
// update_passwords.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== ОБНОВЛЕНИЕ ПАРОЛЕЙ ПОЛЬЗОВАТЕЛЕЙ ===\n\n";

// Подключаемся к базе данных
require_once './includes/db_connect.php';

try {
    // Получаем всех пользователей
    $stmt = $pdo->query("SELECT id, login FROM users");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "Пользователи не найдены.\n";
        exit;
    }
    
    echo "Найдено пользователей: " . count($users) . "\n\n";
    
    // Подготавливаем запрос для обновления
    $updateStmt = $pdo->prepare("
        UPDATE users 
        SET pass = :pass, 
            pass_tmp = :pass_tmp, 
            is_hashed = 1 
        WHERE id = :id
    ");
    
    $updated = 0;
    
    foreach ($users as $user) {
        $login = $user['login'];
        $id = $user['id'];
        
        // Генерируем пароль: логин + 123
        $plainPassword = $login . '123';
        
        // Хешируем пароль для поля pass
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        // Обновляем запись в базе данных
        $updateStmt->execute([
            ':pass' => $hashedPassword,
            ':pass_tmp' => $plainPassword,
            ':id' => $id
        ]);
        
        if ($updateStmt->rowCount() > 0) {
            echo "✓ Обновлен пользователь ID {$id}: {$login}\n";
            echo "  Пароль: {$plainPassword}\n";
            $updated++;
        } else {
            echo "✗ Не удалось обновить пользователя ID {$id}\n";
        }
    }
    
    echo "\nГотово! Обновлено пользователей: {$updated}/" . count($users) . "\n";
    
    // Проверяем результаты
    echo "\n=== ПРОВЕРКА РЕЗУЛЬТАТОВ ===\n\n";
    
    $checkStmt = $pdo->query("
        SELECT id, login, pass, pass_tmp, is_hashed 
        FROM users 
        ORDER BY id
    ");
    
    $results = $checkStmt->fetchAll();
    
    foreach ($results as $user) {
        $isValid = password_verify($user['pass_tmp'], $user['pass']);
        
        echo "ID: {$user['id']} | Login: {$user['login']}\n";
        echo "Pass_tmp: {$user['pass_tmp']}\n";
        echo "Pass hash: " . substr($user['pass'], 0, 20) . "...\n";
        echo "Valid: " . ($isValid ? '✓' : '✗') . " | is_hashed: {$user['is_hashed']}\n";
        echo "----------------------------------\n";
    }
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit;
}

echo "\nОбновление завершено успешно!\n";
echo "Теперь можно войти с паролями вида 'Login123'\n";
?>