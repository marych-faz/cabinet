<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Обработка выхода - без уничтожения сессии
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
            case 'get_partner':
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $partner = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($partner) {
                    $partner['dog_beg_date'] = date('Y-m-d', strtotime($partner['dog_beg_date']));
                    $partner['dog_end_date'] = date('Y-m-d', strtotime($partner['dog_end_date']));
                    echo json_encode($partner);
                } else {
                    echo json_encode(['error' => 'Партнер не найден']);
                }
                break;
                
            case 'update_partner':
                $stmt = $pdo->prepare("UPDATE users SET 
                    name = ?, email = ?, phone = ?,
                    dog_num = ?, dog_beg_date = ?, dog_end_date = ?,
                    bank_name = ?, bank_bik = ?, bank_ks = ?, bank_rs = ?
                    WHERE id = ?");
                
                $stmt->execute([
                    $_POST['name'], $_POST['email'], $_POST['phone'],
                    $_POST['dog_num'], $_POST['dog_beg_date'], $_POST['dog_end_date'],
                    $_POST['bank_name'], $_POST['bank_bik'], $_POST['bank_ks'], $_POST['bank_rs'],
                    $_POST['id']
                ]);
                echo json_encode(['success' => true]);
                break;
                
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

$query = "SELECT id, login, name, email, dog_num, dog_beg_date, dog_end_date, status, is_archived 
          FROM users 
          WHERE is_admin = 0";

if (!$showArchived) $query .= " AND is_archived = 0";
if (!empty($search)) $query .= " AND (name LIKE ? OR email LIKE ? OR login LIKE ? OR dog_num LIKE ?)";
if ($statusFilter !== 'all') $query .= " AND status = ?";
$query .= " ORDER BY $sort $order";

$stmt = $pdo->prepare($query);

$params = [];
if (!empty($search)) {
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
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
                    <th>№ договора</th>
                    <th>Дата начала</th>
                    <th>Дата окончания</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partners as $partner): ?>
                <tr>
                    <td><?= htmlspecialchars($partner['login']) ?></td>
                    <td><?= htmlspecialchars($partner['name']) ?></td>
                    <td><?= htmlspecialchars($partner['email']) ?></td>
                    <td><?= htmlspecialchars($partner['dog_num']) ?></td>
                    <td><?= date('d.m.Y', strtotime($partner['dog_beg_date'])) ?></td>
                    <td><?= date('d.m.Y', strtotime($partner['dog_end_date'])) ?></td>
                    <td><span class="status status-<?= $partner['status'] ?>">
                        <?= $partner['status'] == 0 ? 'Заявка' : ($partner['status'] == 1 ? 'Одобрен' : 'Заблокирован') ?>
                    </span></td>
                    <td>
                        <button class="action-btn edit-btn" data-id="<?= $partner['id'] ?>" title="Редактировать">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
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

    <!-- Модальное окно редактирования -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Редактирование партнера</h2>
                <button class="modal-close" id="cancelBtn">&times;</button>
            </div>
            
            <form id="partnerForm">
                <input type="hidden" id="partnerId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">ФИО</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="tel" id="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="dog_num" class="form-label">№ договора</label>
                        <input type="text" id="dog_num" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="dog_beg_date" class="form-label">Дата начала</label>
                        <input type="date" id="dog_beg_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="dog_end_date" class="form-label">Дата окончания</label>
                        <input type="date" id="dog_end_date" class="form-control" required>
                    </div>
                </div>
                
                <h3 class="section-title">Банковские реквизиты</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bank_bik" class="form-label">БИК</label>
                        <input type="text" id="bank_bik" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="bank_name" class="form-label">Наименование банка</label>
                        <input type="text" id="bank_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bank_rs" class="form-label">Расчетный счет</label>
                        <input type="text" id="bank_rs" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="bank_ks" class="form-label">Корреспондентский счет</label>
                        <input type="text" id="bank_ks" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="saveBtn" class="btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Сохранить
                    </button>
                    <button type="button" id="cancelBtn2" class="btn" style="background: var(--gray);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Закрыть
                    </button>
                </div>
            </form>
        </div>
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
        
        // Редактирование партнера
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const partnerId = this.getAttribute('data-id');
                fetch('partners.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_partner&id=${partnerId}`
                })
                .then(response => response.json())
                .then(partner => {
                    if (partner.error) throw new Error(partner.error);
                    
                    document.getElementById('partnerId').value = partner.id;
                    document.getElementById('name').value = partner.name || '';
                    document.getElementById('email').value = partner.email || '';
                    document.getElementById('phone').value = partner.phone || '';
                    document.getElementById('dog_num').value = partner.dog_num || '';
                    document.getElementById('dog_beg_date').value = partner.dog_beg_date || '';
                    document.getElementById('dog_end_date').value = partner.dog_end_date || '';
                    document.getElementById('bank_bik').value = partner.bank_bik || '';
                    document.getElementById('bank_name').value = partner.bank_name || '';
                    document.getElementById('bank_rs').value = partner.bank_rs || '';
                    document.getElementById('bank_ks').value = partner.bank_ks || '';
                    
                    document.getElementById('editModal').style.display = 'flex';
                })
                .catch(error => {
                    alert('Ошибка загрузки данных: ' + error.message);
                });
            });
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
        
        // Сохранение изменений
        document.getElementById('saveBtn').addEventListener('click', function() {
            const formData = new URLSearchParams();
            formData.append('action', 'update_partner');
            formData.append('id', document.getElementById('partnerId').value);
            formData.append('name', document.getElementById('name').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('dog_num', document.getElementById('dog_num').value);
            formData.append('dog_beg_date', document.getElementById('dog_beg_date').value);
            formData.append('dog_end_date', document.getElementById('dog_end_date').value);
            formData.append('bank_name', document.getElementById('bank_name').value);
            formData.append('bank_bik', document.getElementById('bank_bik').value);
            formData.append('bank_rs', document.getElementById('bank_rs').value);
            formData.append('bank_ks', document.getElementById('bank_ks').value);
            
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
                alert('Данные успешно сохранены');
                location.reload();
            })
            .catch(error => {
                alert('Ошибка сохранения: ' + error.message);
            });
        });
        
        // Закрытие модального окна
        const closeModal = () => document.getElementById('editModal').style.display = 'none';
        document.getElementById('cancelBtn').addEventListener('click', closeModal);
        document.getElementById('cancelBtn2').addEventListener('click', closeModal);
        
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    });
    </script>
</body>
</html>