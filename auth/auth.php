<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

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
        <?php if ($show_register): ?>
            <?php include 'register.php'; ?>
        <?php else: ?>
            <div class="auth-container login">
                <div class="auth-header">
                    <div class="logo">
                        <svg viewBox="0 0 24 24"><path d="M12 2L1 12h3v9h6v-6h4v6h6v-9h3L12 2zm0 2.8L18 10v9h-2v-6H8v6H6v-9l6-7.2z"/></svg>
                    </div>
                    <div class="auth-header-text">
                        <h1 class="auth-title">Добро пожаловать</h1>
                        <p class="auth-subtitle">Введите свои учетные данные</p>
                    </div>
                </div>
                
                <div class="auth-body">
                    <?php if (isset($error)): ?>
                        <div class="status-message status-error">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" class="auth-form">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="form-group">
                            <label for="login" class="form-label">Логин</label>
                            <input type="text" id="login" name="login" class="form-control" placeholder="Введите ваш логин" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        
                        <!-- Ссылка "Забыли пароль?" -->
                        <div class="forgot-password">
                            <a href="forgot_password.php" class="form-link">Забыли пароль?</a>
                        </div>
                        
                        <button type="submit" class="btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" y1="12" x2="3" y2="12"/>
                            </svg>
                            Войти в систему
                        </button>
                        
                        <div class="form-footer">
                            Нет аккаунта? <a href="?register=1" class="form-link">Зарегистрироваться</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>