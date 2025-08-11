<?php
// Включение отображения всех ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Проверка доступности PDO
if (!extension_loaded('pdo')) {
    die('Ошибка: Расширение PDO не установлено на сервере');
}

// Проверка драйвера MySQL для PDO
if (!in_array('mysql', PDO::getAvailableDrivers())) {
    die('Ошибка: Драйвер PDO для MySQL не доступен');
}

// Подключение функции транслитерации
require_once __DIR__ . '/fioToLogin.php';

// Настройки подключения к БД
$host = 'MySQL-8.4';
$dbname = 'cabinet';
$username = 'root';
$password = '';

try {
    // Подключение к базе данных с обработкой ошибок
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

/**
 * Обработка входа пользователя
 */
function handleLogin($pdo) {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT id, name, is_admin, is_archived, pass FROM users WHERE login = :login");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['pass'])) {
            if ($user['is_archived']) {
                echo json_encode(['success' => false, 'message' => 'Пользователь в архиве, свяжитесь с администратором']);
                return;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            $_SESSION['user_name'] = $user['name'];

            $redirect = $user['is_admin'] ? '/src/Admin/admin-form.html' : '/src/Partner/partner-form.html';
            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверный логин или пароль']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
}

/**
 * Обработка регистрации пользователя
 */
function handleRegistration($pdo) {
    // Валидация данных
    $required = ['name', 'login', 'email', 'pass', 'pass_confirm', 'phone', 'dog_num', 'bank_bik'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Поле $field обязательно для заполнения"]);
            return;
        }
    }

    // Проверка паролей
    if ($_POST['pass'] !== $_POST['pass_confirm']) {
        echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
        return;
    }

    // Проверка email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Некорректный email']);
        return;
    }

    // Проверка капчи
    if (!isset($_SESSION['captcha']) || strtoupper($_POST['captcha']) !== strtoupper($_SESSION['captcha'])) {
        echo json_encode(['success' => false, 'message' => 'Неверная CAPTCHA']);
        return;
    }

    try {
        // Хеширование пароля
        $hashedPass = password_hash($_POST['pass'], PASSWORD_DEFAULT);

        // Подготовка данных для вставки
        $data = [
            ':login' => $_POST['login'],
            ':email' => $_POST['email'],
            ':pass' => $hashedPass,
            ':name' => $_POST['name'],
            ':dog_num' => $_POST['dog_num'],
            ':dog_beg_date' => $_POST['dog_beg_date'] ?? date('Y-m-d'),
            ':dog_end_date' => $_POST['dog_end_date'] ?? date('Y-m-d', strtotime('+1 year')),
            ':phone' => $_POST['phone'],
            ':bank_name' => $_POST['bank_name'] ?? '',
            ':bank_bik' => $_POST['bank_bik'],
            ':bank_ks' => $_POST['bank_ks'] ?? '',
            ':bank_rs' => $_POST['bank_rs'] ?? '',
            ':bank_verified' => isset($_POST['bank_verified']) ? 1 : 0
        ];

        // Вставка в базу данных
        $stmt = $pdo->prepare("INSERT INTO users (
            is_admin, login, email, pass, is_hashed, name, dog_num, 
            dog_beg_date, dog_end_date, phone, bank_name, bank_bik, 
            bank_ks, bank_rs, bank_verified, status
        ) VALUES (
            0, :login, :email, :pass, 1, :name, :dog_num, 
            :dog_beg_date, :dog_end_date, :phone, :bank_name, :bank_bik, 
            :bank_ks, :bank_rs, :bank_verified, 0
        )");

        $stmt->execute($data);

        echo json_encode(['success' => true, 'message' => 'Регистрация успешна! Ожидайте подтверждения.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка регистрации: ' . $e->getMessage()]);
    }
}

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'login':
            handleLogin($pdo);
            exit;
        case 'register':
            handleRegistration($pdo);
            exit;
        case 'check_login':
            checkLoginAvailability($pdo);
            exit;
        case 'check_bik':
            checkBankBik($pdo);
            exit;
        case 'verify_captcha':
            verifyCaptcha();
            exit;
    }
}

// Если это не AJAX-запрос, отображаем HTML-форму
displayAuthForm();

/**
 * Отображение HTML-формы авторизации/регистрации
 */
function displayAuthForm() {
    $isLoginPage = !isset($_GET['register']);
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $isLoginPage ? 'Авторизация' : 'Регистрация' ?></title>
        <style>
            /* Стили остаются без изменений */
            /* ... */
        </style>
    </head>
    <body>
        <div class="auth-container">
            <div class="auth-header">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                </svg>
                <h1><?= $isLoginPage ? 'Авторизация' : 'Регистрация' ?></h1>
            </div>
            
            <div class="auth-body">
                <?php if ($isLoginPage): ?>
                    <!-- Форма входа -->
                    <form id="loginForm">
                        <!-- Поля формы входа -->
                    </form>
                    
                    <!-- Форма регистрации (скрыта) -->
                    <form id="registerForm" class="hidden">
                        <!-- Поля формы регистрации -->
                    </form>
                <?php else: ?>
                    <!-- Форма регистрации -->
                    <form id="registerForm">
                        <!-- Поля формы регистрации -->
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <script>
            // JavaScript код остается без изменений
            // ...
        </script>
    </body>
    </html>
    <?php
}

/**
 * Проверка доступности логина
 */
function checkLoginAvailability($pdo) {
    $login = $_POST['login'] ?? '';
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = :login");
        $stmt->execute([':login' => $login]);
        echo json_encode(['available' => $stmt->fetchColumn() == 0]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Проверка БИК банка
 */
function checkBankBik($pdo) {
    $bik = $_POST['bik'] ?? '';
    try {
        $stmt = $pdo->prepare("SELECT name, ks FROM banks WHERE bic = :bik");
        $stmt->execute([':bik' => $bik]);
        $bank = $stmt->fetch();
        
        echo json_encode([
            'success' => !empty($bank),
            'bank_name' => $bank['name'] ?? '',
            'bank_ks' => $bank['ks'] ?? '',
            'verified' => !empty($bank)
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Проверка CAPTCHA
 */
function verifyCaptcha() {
    $userInput = $_POST['captcha'] ?? '';
    if (!isset($_SESSION['captcha'])) {
        echo json_encode(['success' => false, 'message' => 'CAPTCHA не сгенерирована']);
        return;
    }
    echo json_encode(['success' => strtoupper($userInput) === strtoupper($_SESSION['captcha'])]);
}