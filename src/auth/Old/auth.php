<?php
header('Content-Type: application/json');

// Параметры подключения к базе данных
$host = 'MySQL-8.4';
$dbname = 'cabinet';
$username = 'root';
$password = '';

//echo "<script>"
//echo "console.log(__DIR__)"
//echo "</script>"
try {
    // Подключение к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получение данных из POST-запроса
    $login = $_POST['login'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (empty($login) || empty($pass)) {
        throw new Exception('Логин и пароль обязательны для заполнения');
    }

    // Подготовка и выполнение запроса
    $stmt = $pdo->prepare("SELECT id, name, is_admin FROM users WHERE login = :login AND pass = :pass");
    $stmt->bindParam(':login', $login);
    $stmt->bindParam(':pass', $pass);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Успешная авторизация
        echo json_encode([
            'success' => true,
            'is_admin' => $user['is_admin'],
            'message' => 'Авторизация успешна'
        ]);
    } else {
        // Неверные учетные данные
        echo json_encode([
            'success' => false,
            'message' => 'Неверный логин или пароль'
        ]);
    }
} catch (PDOException $e) {
    // Ошибка подключения к базе данных
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка подключения к базе данных: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Другие ошибки
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
