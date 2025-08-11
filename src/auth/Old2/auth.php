<?php
session_start();

$host = 'MySQL-8.4';
$dbname = 'cabinet';
$username = 'root';
$password = '';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['password'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT id, name, is_admin, is_archived, pass FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ((int)$user['is_archived'] === 1) {
                $error = "Пользователь отправлен в архив, свяжитесь с администратором";
            } elseif (password_verify($pass, $user['pass'])) {
                // Успешная авторизация
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = $user['is_admin'];

                echo "<script>
                    sessionStorage.setItem('IsAdmin', '" . $user['is_admin'] . "');
                    window.location.href = '" . ($user['is_admin'] ? __DIR__.'/src/Admin/admin-form.html' : __DIR__.'/src/Partner/partner-form.html') . "';
                </script>";
                exit;
            } else {
                $error = "Неверный логин или пароль";
            }
        } else {
            $error = "Неверный логин или пароль";
        }

    } catch (PDOException $e) {
        $error = "Ошибка базы данных: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 320px;
            text-align: center;
        }
        .user-icon {
            font-size: 50px;
            color: #5cb85c;
            margin-bottom: 15px;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #5cb85c;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #5cb85c;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .error {
            color: red;
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="user-icon">
            <i class="fas fa-user-circle"></i>
        </div>
        <h2>Авторизация</h2>
        <form id="loginForm">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" required placeholder="Введите ваш логин">
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required placeholder="Введите ваш пароль">
            </div>
            <button type="submit">Войти</button>
            <div id="errorMessage" class="error"></div>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const login = document.getElementById('login').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');
            
            errorMessage.textContent = '';
            
            fetch('auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `login=${encodeURIComponent(login)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('IsAdmin', data.is_admin);
                    if (data.is_admin == 1 || data.is_admin === true) {
                        window.location.href = '../Admin/Admin.html';
                    } else {
                        window.location.href = '../Partner/Partner.html';
                    }
                } else {
                    errorMessage.textContent = data.message || 'Ошибка авторизации';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Произошла ошибка при отправке запроса';
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>