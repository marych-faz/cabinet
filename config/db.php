<?php
// Настройки подключения
define('DB_HOST', 'MySQL-8.4');
define('DB_NAME', 'cabinet');
define('DB_USER', 'root');
define('DB_PASS', '');

// Подключение к базе данных
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log('DB connection error: ' . $e->getMessage());
            die('Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.');
        }
    }
    
    return $pdo;
}

// Проверка доступности PDO
function checkPDO() {
    if (!extension_loaded('pdo')) {
        die('Требуется расширение PDO');
    }
    
    if (!in_array('mysql', PDO::getAvailableDrivers())) {
        die('Требуется драйвер PDO для MySQL');
    }
}

// Выполняем проверку при подключении файла
checkPDO();