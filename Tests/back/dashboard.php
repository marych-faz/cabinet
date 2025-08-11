<?php
session_start();

// Защита: если не авторизован — редирект на вход
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = 'Доступ запрещён. Войдите в систему.';
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Добро пожаловать, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
        <p>Вы успешно вошли в систему.</p>

        <!-- Кнопка "Назад" -->
        <button class="btn" onclick="goBack()">← Назад</button>

        <!-- Кнопка "Выход" -->
        <a href="logout.php" class="btn btn-danger">Выход</a>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>