<?php
// Настройка логирования
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/../logs/error.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db_connect.php';

try {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth.php");
        exit();
    }

    // Параметры фильтрации
    $statusFilter = $_GET['status'] ?? 'all';
    $search = trim($_GET['search'] ?? '');

    // Основной запрос
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
              LEFT JOIN act_payments ap ON ap.act_id = a.id";

    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(a.num LIKE ? OR u.name LIKE ? OR a.comment LIKE ?)";
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
    }

    if ($statusFilter !== 'all') {
        $where[] = "a.status = ?";
        $params[] = $statusFilter;
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " GROUP BY a.id, a.partner_id, a.num, a.date, a.status, a.comment, u.name
               ORDER BY a.date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $acts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] DB Error: " . $e->getMessage(), 3, __DIR__.'/../logs/error.log');
    die("Ошибка базы данных. Пожалуйста, попробуйте позже.");
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] System Error: " . $e->getMessage(), 3, __DIR__.'/../logs/error.log');
    die("Системная ошибка. Пожалуйста, попробуйте позже.");
}

// Функция для безопасного отображения статуса
function getStatusText($status) {
    switch ($status) {
        case 0: return 'Черновик';
        case 1: return 'На одобрение';
        case 2: return 'Одобрено';
        case 3: return 'Отклонено';
        default: return 'Неизвестно';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список актов. Администратор</title>
    <link rel="stylesheet" href="acts.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <svg class="header-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <h1>Список актов. Администратор</h1>
        </div>
        <button onclick="window.location.href='admin-form.html'" class="logout-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
            </svg>
            Закрыть
        </button>
    </header>

    <div class="container">
        <div class="filters">
            <form method="get" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search">Поиск</label>
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Номер или название">
                    </div>
                    <div class="filter-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Все статусы</option>
                            <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Черновик</option>
                            <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>На одобрение</option>
                            <option value="2" <?= $statusFilter === '2' ? 'selected' : '' ?>>Одобрено</option>
                            <option value="3" <?= $statusFilter === '3' ? 'selected' : '' ?>>Отклонено</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="apply-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                            </svg>
                            Применить
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="acts-table">
                <thead>
                    <tr>
                        <th>№ акта</th>
                        <th>Дата</th>
                        <th>Партнер</th>
                        <th>Статус</th>
                        <th>Позиций</th>
                        <th class="text-right">Размещение</th>
                        <th class="text-right">Платеж</th>
                        <th class="text-right">Комиссия</th>
                        <th class="text-right">Оплачено</th>
                        <th>Комментарий</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($acts)): ?>
                        <?php foreach ($acts as $index => $act): ?>
                        <tr class="<?= $index % 2 === 0 ? 'even' : 'odd' ?>">
                            <td><?= htmlspecialchars($act['num']) ?></td>
                            <td><?= date('d.m.Y', strtotime($act['date'])) ?></td>
                            <td><?= htmlspecialchars($act['partner_name']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $act['status'] ?>">
                                    <?= getStatusText($act['status']) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $act['detail_count'] ?></td>
                            <td class="text-right"><?= number_format($act['total_placement_amount'], 2, '.', ' ') ?></td>
                            <td class="text-right"><?= number_format($act['total_operator_payment'], 2, '.', ' ') ?></td>
                            <td class="text-right"><?= number_format($act['total_commission_amount'], 2, '.', ' ') ?></td>
                            <td class="text-right"><?= number_format($act['total_payments'], 2, '.', ' ') ?></td>
                            <td><?= htmlspecialchars($act['comment']) ?></td>
                            <td class="actions">
                                <button class="edit-btn" data-id="<?= $act['id'] ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="no-data">Нет данных для отображения</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка кнопки редактирования
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const actId = this.getAttribute('data-id');
                window.location.href = `act-edit.php?id=${actId}`;
            });
        });
    });
    </script>
</body>
</html>