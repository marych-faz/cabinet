<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

$response = ['success' => false, 'name' => ''];

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            $response['success'] = true;
            $response['name'] = $user['name'];
        }
    } catch (PDOException $e) {
        // Логирование ошибки
        error_log("Ошибка при получении данных пользователя: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>