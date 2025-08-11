<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

// Получение параметров фильтрации
$statusFilter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'date';
$order = $_GET['order'] ?? 'desc';

// SQL запрос для получения актов
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
          LEFT JOIN users u ON u.id = a.partner_id
          LEFT JOIN act_detail ad ON ad.act_id = a.id
          LEFT JOIN act_payments ap ON ap.act_id = a.id
          WHERE a.partner_id = :partner_id";

$params = [':partner_id' => $_SESSION['user_id']];

// Добавление условий фильтрации
if ($statusFilter !== 'all') {
    $query .= " AND a.status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($search)) {
    $query .= " AND (a.num LIKE :search OR a.comment LIKE :search OR u.name LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " GROUP BY a.id, a.partner_id, u.name, a.num, a.date, a.status, a.comment";
$query .= " ORDER BY a.date DESC, u.name ASC";

// Выполнение запроса
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$acts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка AJAX-действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo->beginTransaction();
        
        switch ($_POST['action']) {
            case 'delete_act':
                // Проверка наличия платежей
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM act_payments WHERE act_id = ?");
                $stmt->execute([$_POST['id']]);
                $hasPayments = $stmt->fetchColumn() > 0;
                
                if ($hasPayments) {
                    echo json_encode(['error' => 'Нельзя удалить акт - по нему есть оплаты.']);
                } else {
                    // Удаление из act_detail
                    $stmt = $pdo->prepare("DELETE FROM act_detail WHERE act_id = ?");
                    $stmt->execute([$_POST['id']]);
                    
                    // Удаление акта
                    $stmt = $pdo->prepare("DELETE FROM acts WHERE id = ? AND partner_id = ?");
                    $stmt->execute([$_POST['id'], $_SESSION['user_id']]);
                    
                    echo json_encode(['success' => true]);
                }
                break;
                
            case 'archive_act':
                $stmt = $pdo->prepare("UPDATE acts SET status = 3 WHERE id = ? AND partner_id = ?");
                $stmt->execute([$_POST['id'], $_SESSION['user_id']]);
                echo json_encode(['success' => true]);
                break;
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список актов</title>
    <link rel="stylesheet" href="orgs.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <svg class="header-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4"/>
            </svg>
            <h1>Список актов. Партнер</h1>
        </div>
        <button onclick="window.location.href='partner-form.html'" class="logout-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
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
                <button id="applyFilters" class="btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Применить
                </button>
                <button id="newActBtn" class="btn" style="margin-left: 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Новый акт
                </button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID партнера</th>
                    <th>Партнер</th>
                    <th>Номер акта</th>
                    <th>Дата акта</th>
                    <th>Статус</th>
                    <th>Комментарий</th>
                    <th>Кол-во позиций</th>
                    <th>Сумма размещения</th>
                    <th>Платеж оператора</th>
                    <th>Сумма комиссии</th>
                    <th>Сумма оплаты</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($acts as $index => $act): ?>
                <tr class="<?= $index % 2 === 0 ? 'even' : 'odd' ?>">
                    <td><?= htmlspecialchars($act['id']) ?></td>
                    <td><?= htmlspecialchars($act['partner_id']) ?></td>
                    <td><?= htmlspecialchars($act['partner_name']) ?></td>
                    <td><?= htmlspecialchars($act['num']) ?></td>
                    <td><?= date('d.m.Y', strtotime($act['date'])) ?></td>
                    <td>
                        <span class="status status-<?= $act['status'] ?>">
                            <?= 
                                $act['status'] == 0 ? 'Черновик' : 
                                ($act['status'] == 1 ? 'На одобрение' : 
                                ($act['status'] == 2 ? 'Одобрено' : 'Архив')) 
                            ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($act['comment']) ?></td>
                    <td><?= $act['detail_count'] ?></td>
                    <td><?= number_format($act['total_placement_amount'], 2, '.', ' ') ?></td>
                    <td><?= number_format($act['total_operator_payment'], 2, '.', ' ') ?></td>
                    <td><?= number_format($act['total_commission_amount'], 2, '.', ' ') ?></td>
                    <td><?= number_format($act['total_payments'], 2, '.', ' ') ?></td>
                    <td>
                        <button class="action-btn edit-btn" data-id="<?= $act['id'] ?>" title="Редактировать">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="action-btn archive-btn" data-id="<?= $act['id'] ?>" title="В архив">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </button>
                        <button class="action-btn delete-btn" data-id="<?= $act['id'] ?>" title="Удалить">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Применение фильтров
        function applyFilters() {
            const params = new URLSearchParams();
            params.set('search', document.getElementById('search').value);
            params.set('status', document.getElementById('status').value);
            window.location.search = params.toString();
        }
        
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyFilters();
        });
        
        // Создание нового акта
        document.getElementById('newActBtn').addEventListener('click', function() {
            window.location.href = 'act-edit.php?new=1';
        });
        
        // Редактирование акта
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const actId = this.getAttribute('data-id');
                window.location.href = `act-edit.php?id=${actId}`;
            });
        });
        
        // Архивация акта
        document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите отправить акт в архив?')) {
                    fetch('acts.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=archive_act&id=${this.getAttribute('data-id')}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) throw new Error(data.error);
                        location.reload();
                    })
                    .catch(error => {
                        alert('Ошибка: ' + error.message);
                    });
                }
            });
        });
        
        // Удаление акта
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите удалить акт?')) {
                    fetch('acts.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete_act&id=${this.getAttribute('data-id')}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error); // Показываем сообщение об ошибке
                        } else if (data.success) {
                            location.reload(); // Перезагружаем страницу при успешном удалении
                        }
                    })
                    .catch(error => {
                        alert('Ошибка: ' + error);
                    });
                }
            });
        });
    });
    </script>
</body>
</html>