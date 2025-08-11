<?php
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['bik']) && strlen($_GET['bik']) === 9) {
    try {
        $stmt = $pdo->prepare("SELECT name, ks FROM bank WHERE bic = ?");
        $stmt->execute([$_GET['bik']]);
        $bank = $stmt->fetch();
        
        if ($bank) {
            echo json_encode([
                'success' => true,
                'name' => $bank['name'],
                'ks' => $bank['ks']
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false]);
}