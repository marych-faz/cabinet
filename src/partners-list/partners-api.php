<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Убедимся, что это не OPTIONS запрос (для CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$host = 'MySQL-8.4';
$dbname = 'cabinet';
$username = 'root';
$password = '';

error_reporting();
echo __FILE__;
echo $_SERVER['SERVER_NAME'];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Обработка GET запросов
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'getAll') {
            $stmt = $conn->prepare("SELECT * FROM users WHERE is_admin = 0");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $results
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // Обработка POST запросов
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON data'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $action = $data['action'] ?? '';
        
        if ($action === 'toggleArchive') {
            $id = $data['id'];
            $is_archived = $data['is_archived'];
            
            $stmt = $conn->prepare("UPDATE users SET is_archived = :is_archived WHERE id = :id");
            $stmt->bindParam(':is_archived', $is_archived, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $success = $stmt->execute();
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Статус обновлен' : 'Ошибка обновления статуса'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if ($action === 'update') {
            $id = $data['id'];
            unset($data['action'], $data['id']);
            
            $setParts = [];
            foreach ($data as $key => $value) {
                $setParts[] = "$key = :$key";
            }
            $setClause = implode(', ', $setParts);
            
            $sql = "UPDATE users SET $setClause WHERE id = :id";
            $stmt = $conn->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            $success = $stmt->execute();
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Данные обновлены' : 'Ошибка обновления данных'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Неизвестное действие'
    ], JSON_UNESCAPED_UNICODE);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка базы данных: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
