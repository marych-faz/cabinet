<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Получение данных пользователя
$user = null;
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception("Пользователь не найден");
    }
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save') {
        try {
            // Валидация данных
            $login = trim($_POST['login'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $dog_num = trim($_POST['dog_num'] ?? '');
            $dog_beg_date = $_POST['dog_beg_date'] ?? '';
            $dog_end_date = $_POST['dog_end_date'] ?? '';
            $bank_bik = trim($_POST['bank_bik'] ?? '');
            $bank_name = trim($_POST['bank_name'] ?? '');
            $bank_ks = trim($_POST['bank_ks'] ?? '');
            $bank_rs = trim($_POST['bank_rs'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Проверка логина
            if (empty($login)) {
                throw new Exception("Логин не может быть пустым");
            }

            if ($login !== $user['login']) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
                $stmt->execute([$login]);
                if ($stmt->fetch()) {
                    throw new Exception("Этот логин уже занят");
                }
            }

            // Проверка пароля
            $hashed_password = $user['pass'];
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    throw new Exception("Пароли не совпадают");
                }
                if (strlen($password) < 8) {
                    throw new Exception("Пароль должен содержать минимум 8 символов");
                }
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            }

            // Обновление данных
            $stmt = $pdo->prepare("UPDATE users SET 
                login = ?, 
                email = ?, 
                phone = ?, 
                pass = ?, 
                is_hashed = 1, 
                dog_num = ?, 
                dog_beg_date = ?, 
                dog_end_date = ?, 
                bank_bik = ?, 
                bank_name = ?, 
                bank_ks = ?, 
                bank_rs = ? 
                WHERE id = ?");
            
            $stmt->execute([
                $login,
                $email,
                $phone,
                $hashed_password,
                $dog_num,
                $dog_beg_date,
                $dog_end_date,
                $bank_bik,
                $bank_name,
                $bank_ks,
                $bank_rs,
                $_SESSION['user_id']
            ]);

            $success = "Данные успешно сохранены";
            
            // Обновляем данные пользователя
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль партнера</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-wrapper">
        <div class="profile-header">
            <h1 class="profile-title">Профиль партнера</h1>
            <p class="profile-subtitle">Редактирование данных</p>
        </div>
        
        <div class="profile-body">
            <?php if ($error): ?>
                <div class="status-message status-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="status-message status-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($user): ?>
            <form method="post" class="profile-form">
                <input type="hidden" name="action" value="save">
                
                <div class="form-section">
                    <h3 class="section-title">Основные данные</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ФИО</label>
                            <input type="text" class="form-control readonly-field" 
                                   value="<?= htmlspecialchars($user['name']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Логин</label>
                            <input type="text" class="form-control readonly-field" 
                                   value="<?= htmlspecialchars($user['login']) ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Телефон</label>
                            <div class="phone-input-container">
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?= htmlspecialchars($user['phone']) ?>" required>
                                <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $user['phone'])) ?>" class="phone-call">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Смена пароля</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Оставьте пустым, если не нужно менять">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="password" name="confirm_password" class="form-control" 
                                   placeholder="Повторите новый пароль">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Данные договора</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Номер договора</label>
                            <input type="text" name="dog_num" class="form-control" 
                                   value="<?= htmlspecialchars($user['dog_num']) ?>" required>
                        </div>
                    </div>
                    <div class="dates-row">
                        <div class="form-group">
                            <label class="form-label">Дата начала</label>
                            <input type="date" name="dog_beg_date" class="form-control" 
                                   value="<?= htmlspecialchars($user['dog_beg_date']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Дата окончания</label>
                            <input type="date" name="dog_end_date" class="form-control" 
                                   value="<?= htmlspecialchars($user['dog_end_date']) ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Банковские реквизиты</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">БИК</label>
                            <input type="text" name="bank_bik" class="form-control" 
                                   value="<?= htmlspecialchars($user['bank_bik']) ?>" required
                                   onblur="fetchBankDetails()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Наименование банка</label>
                            <input type="text" name="bank_name" class="form-control" 
                                   value="<?= htmlspecialchars($user['bank_name']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Расчетный счет</label>
                            <input type="text" name="bank_rs" class="form-control" 
                                   value="<?= htmlspecialchars($user['bank_rs']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Корреспондентский счет</label>
                            <input type="text" name="bank_ks" class="form-control" 
                                   value="<?= htmlspecialchars($user['bank_ks']) ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Сохранить
                    </button>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Выйти
                    </a>
                </div>
            </form>
            <?php else: ?>
                <div class="status-message status-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>Не удалось загрузить данные пользователя</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function fetchBankDetails() {
        const bik = document.querySelector('input[name="bank_bik"]').value;
        if (bik.length === 9) {
            fetch('get_bank_details.php?bik=' + bik)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('input[name="bank_name"]').value = data.name;
                        document.querySelector('input[name="bank_ks"]').value = data.ks;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }
    </script>
</body>
</html>