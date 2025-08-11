<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'user_name' => ''];

try {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $response = [
                'success' => true,
                'user_name' => $user['name']
            ];
        }
    }
} catch (PDOException $e) {
    $response['error'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>