<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Всегда очищаем сессию при заходе на сайт
$_SESSION = [];

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $stmt = $pdo->prepare("SELECT id, is_admin, pass FROM users WHERE login = ?");
        $stmt->execute([$login]);
        
        if ($user = $stmt->fetch()) {
            if (password_verify($password, $user['pass'])) {
                // Устанавливаем сессию только при успешном входе
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = (bool)$user['is_admin'];
                
                // Перенаправляем после успешного входа
                $redirect = $_SESSION['is_admin'] 
                    ? '/admin/admin-form.html' 
                    : '/partner/partner-form.html';
                header("Location: $redirect");
                exit();
            } else {
                $error = "Неверный логин или пароль";
            }
        } else {
            $error = "Пользователь не найден";
        }
    } catch (PDOException $e) {
        $error = "Ошибка базы данных: " . $e->getMessage();
    }
}

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    // ... (ваш существующий код регистрации без изменений)
}

// Всегда показываем форму авторизации, если явно не запрошена регистрация
$show_register = isset($_GET['register']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $show_register ? 'Регистрация' : 'Авторизация' ?></title>
    <link rel="stylesheet" href="auth.css">
    <script src="auth.js"></script>
</head>
<body>
    <div class="container">
        <?php include($show_register ? 'register.php' : 'login.php'); ?>
    </div>
</body>
</html>