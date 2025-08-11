<?php
try {
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

    // Валидация логина
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

    // Валидация пароля
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
?>