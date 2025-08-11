<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

$response = ['success' => false, 'user_name' => ''];

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $response = [
                'success' => true,
                'user_name' => $user['name']
            ];
        }
    } catch (PDOException $e) {
        // Логирование ошибки
        error_log("Database error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>