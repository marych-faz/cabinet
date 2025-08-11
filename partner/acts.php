<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// 1. ПРОВЕРКА АВТОРИЗАЦИИ
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

// 2. НАСТРОЙКА ПЕРИОДА ПО УМОЛЧАНИЮ (ТЕКУЩИЙ МЕСЯЦ)
$defaultStart = date('Y-m-01');
$defaultEnd = date('Y-m-t');

// 3. ПОЛУЧЕНИЕ ПАРАМЕТРОВ ФИЛЬТРАЦИИ
$statusFilter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// 4. ОБРАБОТКА ПЕРИОДА ИЗ GET-ПАРАМЕТРОВ
$periodStart = $_GET['periodStart'] ?? $defaultStart;
$periodEnd = $_GET['periodEnd'] ?? $defaultEnd;

// Валидация дат
if (!strtotime($periodStart)) $periodStart = $defaultStart;
if (!strtotime($periodEnd)) $periodEnd = $defaultEnd;

// Форматирование для SQL
$periodStart = date('Y-m-d', strtotime($periodStart));
$periodEnd = date('Y-m-d', strtotime($periodEnd));

// 5. ЗАПРОС К БАЗЕ ДАННЫХ
try {
    $query = "SELECT 
                a.id,
                a.partner_id,
                u.name AS partner_name,
                a.num,
                a.date,
                a.status,
                a.comment,
                COUNT(ad.id) AS detail_count,
                COALESCE(SUM(ad.placement_amount), 0) AS total_placement_amount,
                COALESCE(SUM(ad.operator_payment), 0) AS total_operator_payment,
                COALESCE(SUM(ad.commission_amount), 0) AS total_commission_amount,
                COALESCE(SUM(ap.summa), 0) AS total_payments
              FROM acts a
              JOIN users u ON u.id = a.partner_id
              LEFT JOIN act_detail ad ON ad.act_id = a.id
              LEFT JOIN act_payments ap ON ap.act_id = a.id
              WHERE a.partner_id = :partner_id
              AND a.date BETWEEN :periodStart AND :periodEnd";

    $params = [
        ':partner_id' => $_SESSION['user_id'],
        ':periodStart' => $periodStart,
        ':periodEnd' => $periodEnd
    ];

    // Добавляем условия фильтрации
    if ($statusFilter !== 'all') {
        $query .= " AND a.status = :status";
        $params[':status'] = $statusFilter;
    }

    if (!empty($search)) {
        $query .= " AND (a.num LIKE :search OR a.comment LIKE :search OR u.name LIKE :search)";
        $params[':search'] = "%$search%";
    }

    $query .= " GROUP BY a.id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $acts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

// 6. ОБРАБОТКА AJAX-ЗАПРОСОВ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false];
    
    try {
        $pdo->beginTransaction();
        
        switch ($_POST['action']) {
            case 'delete_act':
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM act_payments WHERE act_id = ?");
                $stmt->execute([$_POST['id']]);
                if ($stmt->fetchColumn() > 0) {
                    $response['error'] = 'Нельзя удалить акт с оплатами';
                } else {
                    $pdo->prepare("DELETE FROM act_detail WHERE act_id = ?")->execute([$_POST['id']]);
                    $pdo->prepare("DELETE FROM acts WHERE id = ?")->execute([$_POST['id']]);
                    $response['success'] = true;
                }
                break;
                
            case 'archive_act':
                $pdo->prepare("UPDATE acts SET status = 3 WHERE id = ?")->execute([$_POST['id']]);
                $response['success'] = true;
                break;
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['error'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список актов</title>
    <link rel="stylesheet" href="acts.css">
    <style>
        .status-0 { background: #fff3cd; color: #856404; }
        .status-1 { background: #cce5ff; color: #004085; }
        .status-2 { background: #d4edda; color: #155724; }
        .status-3 { background: #f8f9fa; color: #6c757d; }
        .status { padding: 3px 8px; border-radius: 12px; font-size: 14px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <svg class="header-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4"/>
            </svg>
            <h1>Список актов. Партнер</h1>
        </div>
        <div class="header-center">
            Период: <?= date('d.m.Y', strtotime($periodStart)) ?> - <?= date('d.m.Y', strtotime($periodEnd)) ?>
        </div>
        <button onclick="window.location.href='partner-form.html'" class="logout-btn">
            Закрыть
        </button>
    </header>

    <div class="container">
        <div class="filters">
            <div class="filter-group">
                <label for="search">Поиск</label>
                <input type="text" id="search" placeholder="Поиск..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group">
                <label for="status">Статус</label>
                <select id="status">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Все</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Черновик</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>На одобрение</option>
                    <option value="2" <?= $statusFilter === '2' ? 'selected' : '' ?>>Одобрено</option>
                    <option value="3" <?= $statusFilter === '3' ? 'selected' : '' ?>>Архив</option>
                </select>
            </div>
            <div class="filter-group">
                <button id="applyFilters" class="btn">Применить</button>
                <button id="newActBtn" class="btn">Новый акт</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Номер акта</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>Комментарий</th>
                    <th>Позиций</th>
                    <th>Сумма</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($acts)): ?>
                    <?php foreach ($acts as $act): ?>
                    <tr>
                        <td><?= $act['id'] ?></td>
                        <td><?= htmlspecialchars($act['num']) ?></td>
                        <td><?= date('d.m.Y', strtotime($act['date'])) ?></td>
                        <td>
                            <span class="status status-<?= $act['status'] ?>">
                                <?= match((int)$act['status']) {
                                    0 => 'Черновик',
                                    1 => 'На одобрение',
                                    2 => 'Одобрено',
                                    3 => 'Архив',
                                    default => 'Неизвестно'
                                } ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($act['comment']) ?></td>
                        <td><?= $act['detail_count'] ?></td>
                        <td><?= number_format($act['total_placement_amount'], 2, '.', ' ') ?></td>
                        <td>
                            <button class="action-btn edit-btn" data-id="<?= $act['id'] ?>">✏️</button>
                            <button class="action-btn archive-btn" data-id="<?= $act['id'] ?>">📁</button>
                            <button class="action-btn delete-btn" data-id="<?= $act['id'] ?>">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Нет данных для отображения</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Восстановление периода из localStorage
        const savedStart = localStorage.getItem('periodStart');
        const savedEnd = localStorage.getItem('periodEnd');
        
        // Если есть сохраненный период и нет параметров в URL
        if (savedStart && savedEnd && !window.location.search.includes('periodStart')) {
            const params = new URLSearchParams(window.location.search);
            params.set('periodStart', savedStart);
            params.set('periodEnd', savedEnd);
            window.location.search = params.toString();
        }

        // Применение фильтров
        document.getElementById('applyFilters').addEventListener('click', function() {
            const params = new URLSearchParams();
            params.set('search', document.getElementById('search').value);
            params.set('status', document.getElementById('status').value);
            params.set('periodStart', '<?= $periodStart ?>');
            params.set('periodEnd', '<?= $periodEnd ?>');
            window.location.search = params.toString();
        });

        // Создание нового акта
        document.getElementById('newActBtn').addEventListener('click', function() {
            window.location.href = 'act-edit.php?new=1';
        });

        // Обработка кнопок действий
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                window.location.href = `act-edit.php?id=${btn.dataset.id}`;
            });
        });

        document.querySelectorAll('.archive-btn, .delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.classList.contains('archive-btn') ? 'archive_act' : 'delete_act';
                const message = action === 'archive_act' 
                    ? 'Отправить акт в архив?' 
                    : 'Удалить акт? Это действие нельзя отменить!';
                
                if (confirm(message)) {
                    fetch('acts.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `action=${action}&id=${this.dataset.id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.error || 'Ошибка операции');
                        }
                    })
                    .catch(err => alert('Ошибка сети: ' + err));
                }
            });
        });
    });
    </script>
</body>
</html>