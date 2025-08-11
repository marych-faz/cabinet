<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: /auth.php");
    exit();
}

// Fetch current user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $error = "Пользователь не найден";
}

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($name) || empty($login) || empty($email) || empty($phone)) {
        $error = "Все обязательные поля должны быть заполнены";
    } elseif ($password !== $confirm_password) {
        $error = "Пароли не совпадают";
    } elseif (!empty($password) && strlen($password) < 8) {
        $error = "Пароль должен содержать не менее 8 символов";
    } else {
        try {
            // Check if login is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ? AND id != ?");
            $stmt->execute([$login, $user_id]);
            
            if ($stmt->fetch()) {
                $error = "Логин уже занят другим пользователем";
            } else {
                // Prepare update query
                $updateFields = [
                    'name' => $name,
                    'login' => $login,
                    'email' => $email,
                    'phone' => $phone
                ];
                
                // Update password if changed
                if (!empty($password)) {
                    $updateFields['pass'] = password_hash($password, PASSWORD_DEFAULT);
                    $updateFields['is_hashed'] = 1;
                }
                
                // Build SQL
                $setParts = [];
                $params = [];
                foreach ($updateFields as $field => $value) {
                    $setParts[] = "`$field` = ?";
                    $params[] = $value;
                }
                $params[] = $user_id;
                
                $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                $success = "Данные успешно сохранены";
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error = "Ошибка базы данных: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="logo">
                <svg viewBox="0 0 24 24"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h1 class="profile-title">Редактирование профиля</h1>
            <p class="profile-subtitle">Измените необходимые данные</p>
        </div>
        
        <div class="profile-body">
            <?php if ($error): ?>
                <div class="status-message status-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="status-message status-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="post" class="profile-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">ФИО</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($user['name'] ?? '') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login" class="form-label">Логин</label>
                        <input type="text" id="login" name="login" class="form-control" 
                               value="<?= htmlspecialchars($user['login'] ?? '') ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                               required>
                    </div>
                </div>
                
                <h3 class="section-title">Смена пароля</h3>
                <p class="section-subtitle">Оставьте поля пустыми, если не хотите менять пароль</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">Новый пароль</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Не менее 8 символов">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Подтверждение пароля</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Повторите пароль">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Сохранить
                    </button>
                    
                    <button type="button" onclick="window.history.back()" class="btn btn-secondary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Выйти
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>