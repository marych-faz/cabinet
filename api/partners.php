<?php
require_once __DIR__ . '/../includes/db_connect.php';
header('Content-Type: application/json');

session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
    exit();
}

// Получение данных одного партнера
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_admin = 0");
    $stmt->execute([$_GET['id']]);
    $partner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($partner) {
        echo json_encode($partner);
    } else {
        echo json_encode(['success' => false, 'error' => 'Партнер не найден']);
    }
    exit();
}

// Проверка прав администратора
if (!$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно прав']);
    exit();
}

// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        switch ($data['action']) {
            case 'update_partner':
                $stmt = $pdo->prepare("UPDATE users SET 
                    name = :name,
                    email = :email,
                    phone = :phone,
                    dog_num = :dog_num,
                    dog_beg_date = :dog_beg_date,
                    dog_end_date = :dog_end_date,
                    bank_name = :bank_name,
                    bank_bik = :bank_bik,
                    bank_ks = :bank_ks,
                    bank_rs = :bank_rs
                    WHERE id = :id AND is_admin = 0");
                
                $stmt->execute([
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'dog_num' => $data['dog_num'],
                    'dog_beg_date' => $data['dog_beg_date'],
                    'dog_end_date' => $data['dog_end_date'],
                    'bank_name' => $data['bank_name'],
                    'bank_bik' => $data['bank_bik'],
                    'bank_ks' => $data['bank_ks'],
                    'bank_rs' => $data['bank_rs']
                ]);
                
                echo json_encode(['success' => true]);
                break;
                
            case 'change_status':
                $stmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id AND is_admin = 0");
                $stmt->execute([
                    'id' => $data['id'],
                    'status' => $data['status']
                ]);
                echo json_encode(['success' => true]);
                break;
                
            case 'toggle_archive':
                $stmt = $pdo->prepare("UPDATE users SET is_archived = :is_archived, archived_date = CURRENT_DATE() WHERE id = :id AND is_admin = 0");
                $stmt->execute([
                    'id' => $data['id'],
                    'is_archived' => $data['is_archived']
                ]);
                echo json_encode(['success' => true]);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Неверный запрос']);
?>