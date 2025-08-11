<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';
// Очистка сессии при каждом запуске для отладки (удалите в продакшене)
session_unset();

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Получаем пользователя с хешем пароля
        $stmt = $pdo->prepare("SELECT id, is_admin, pass FROM users WHERE login = ?");
        $stmt->execute([$login]);
        
        if ($user = $stmt->fetch()) {
            // Проверяем пароль через password_verify()
            if (password_verify($password, $user['pass'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = (bool)$user['is_admin'];
                
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

// Проверка авторизации (редирект если уже авторизован)
if (isset($_SESSION['user_id'])) {
    $redirect = $_SESSION['is_admin'] 
        ? '/admin/admin-form.html' 
        : '/partner/partner-form.html';
    header("Location: $redirect");
    exit();
}

// Определение типа формы
$show_register = isset($_GET['register']) || (isset($_POST['action']) && $_POST['action'] === 'register');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $show_register ? 'Регистрация' : 'Авторизация' ?></title>
    <link rel="stylesheet" href="styles.css">
    <script>
    function generateLogin() {
        const name = document.getElementById('name').value.trim();
        if (!name) return;
        
        // Упрощенная транслитерация (реализуйте fioToLogin.js для полной версии)
        const parts = name.split(' ');
        let login = parts[0].toLowerCase();
        if (parts[1]) login += parts[1][0].toUpperCase();
        if (parts[2]) login += parts[2][0].toUpperCase();
        
        document.getElementById('login').value = login;
        checkLogin(login);
    }
    
    function checkLogin(login) {
        fetch('check_login.php?login=' + encodeURIComponent(login))
            .then(r => r.json())
            .then(data => {
                const status = document.getElementById('login-status');
                status.textContent = data.available ? '✓ Доступен' : '✗ Занят';
                status.style.color = data.available ? 'green' : 'red';
            });
    }
    
    function fetchBank() {
        const bik = document.getElementById('bank_bik').value;
        if (bik.length !== 9) return;
        
        fetch('get_bank.php?bik=' + bik)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('bank_name').value = data.bank.name;
                    document.getElementById('bank_ks').value = data.bank.ks;
                }
            });
    }
    
    function refreshCaptcha() {
        document.getElementById('captcha-image').src = 'captcha.php?t=' + Date.now();
    }
    </script>
</head>
<body>
    <div class="container">
        <?php include($show_register ? 'register.php' : 'login.php'); ?>
    </div>
</body>
</html>