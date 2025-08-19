<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Обработка выхода
if (isset($_GET['logout'])) {
    header("Location: admin-form.html");
    exit();
}

// Обработка AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo->beginTransaction();
        
        switch ($_POST['action']) {
            case 'change_status':
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$_POST['status'], $_POST['id']]);
                echo json_encode(['success' => true]);
                break;
                
            case 'toggle_archive':
                $stmt = $pdo->prepare("UPDATE users SET is_archived = ? WHERE id = ?");
                $stmt->execute([$_POST['is_archived'], $_POST['id']]);
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

// Получение списка партнеров
$showArchived = isset($_GET['archived']) && $_GET['archived'] == '1';
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'name';
$order = $_GET['order'] ?? 'asc';

$query = "SELECT u.id, u.login, u.name, u.email, u.status, u.is_archived,
                 COUNT(uc.id) as contract_count,
                 MAX(uc.end_date) as latest_end_date
          FROM users u
          LEFT JOIN user_contracts uc ON u.id = uc.user_id
          WHERE u.is_admin = 0";

if (!$showArchived) $query .= " AND u.is_archived = 0";
if (!empty($search)) $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.login LIKE ?)";
if ($statusFilter !== 'all') $query .= " AND u.status = ?";

$query .= " GROUP BY u.id";
$query .= " ORDER BY $sort $order";

$stmt = $pdo->prepare($query);

$params = [];
if (!empty($search)) {
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}
if ($statusFilter !== 'all') $params[] = $statusFilter;

$stmt->execute($params);
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление партнерами</title>
    <link rel="stylesheet" href="partners.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="admin-form.html" class="details-icon" title="На главную">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </a>
        </div>
        <h1>Управление партнерами</h1>
        <a href="?logout=1" class="logout-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Выход
        </a>
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
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Заявка</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Одобрен</option>
                    <option value="2" <?= $statusFilter === '2' ? 'selected' : '' ?>>Заблокирован</option>
                </select>
            </div>
            <div class="filter-group checkbox-group">
                <input type="checkbox" id="showArchived" <?= $showArchived ? 'checked' : '' ?>>
                <label for="showArchived">Показать архивных</label>
            </div>
            <div class="filter-group">
                <button id="applyFilters" class="btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Применить
                </button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Логин</th>
                    <th>ФИО</th>
                    <th>Email</th>
                    <th>Договоры</th>
                    <th>Действует до</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partners as $partner): 
                    $contractInfo = '';
                    $latestDate = '';
                    
                    if ($partner['contract_count'] > 0) {
                        $contractInfo = $partner['contract_count'] . ' договор(ов)';
                        if ($partner['latest_end_date']) {
                            $latestDate = date('d.m.Y', strtotime($partner['latest_end_date']));
                            // Проверяем, не истек ли срок договора
                            $isExpired = strtotime($partner['latest_end_date']) < time();
                        }
                    } else {
                        $contractInfo = 'Нет договоров';
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($partner['login']) ?></td>
                    <td><?= htmlspecialchars($partner['name']) ?></td>
                    <td><?= htmlspecialchars($partner['email']) ?></td>
                    <td><?= $contractInfo ?></td>
                    <td <?= isset($isExpired) && $isExpired ? 'style="color: var(--danger); font-weight: bold;"' : '' ?>>
                        <?= $latestDate ?: '—' ?>
                    </td>
                    <td><span class="status status-<?= $partner['status'] ?>">
                        <?= $partner['status'] == 0 ? 'Заявка' : ($partner['status'] == 1 ? 'Одобрен' : 'Заблокирован') ?>
                    </span></td>
                    <td>
                        <a href="partner-edit.php?id=<?= $partner['id'] ?>" class="action-btn edit-btn" title="Редактировать">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </a>
                        <button class="action-btn archive-btn" data-id="<?= $partner['id'] ?>" title="<?= $partner['is_archived'] ? 'Восстановить' : 'В архив' ?>">
                            <?= $partner['is_archived'] ? 
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>' : 
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>' ?>
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
            params.set('archived', document.getElementById('showArchived').checked ? '1' : '0');
            window.location.search = params.toString();
        }
        
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyFilters();
        });
        
        // Архивирование/восстановление
        document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.innerHTML.includes('upload') ? 'Восстановить' : 'В архив';
                if (confirm(`Вы уверены, что хотите переместить партнера ${action}?`)) {
                    const formData = new URLSearchParams();
                    formData.append('action', 'toggle_archive');
                    formData.append('id', this.getAttribute('data-id'));
                    formData.append('is_archived', this.innerHTML.includes('upload') ? '0' : '1');
                    
                    fetch('partners.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: formData
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
    });
    </script>
</body>
</html>