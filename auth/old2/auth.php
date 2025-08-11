<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Проверка авторизации
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['is_admin'] ? "../src/Admin/admin-form.html" : "../src/Partner/partner-form.html"));
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