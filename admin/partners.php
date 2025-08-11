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
    <style>
    :root {
        --primary: #4361ee;
        --primary-dark: #3a56d4;
        --text: #2b2d42;
        --light: #f8f9fa;
        --gray: #adb5bd;
        --danger: #ef233c;
        --success: #4cc9f0;
        --warning: #ff9e00;
    }
    
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
        min-height: 100vh;
        padding: 20px;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .header h1 {
        font-size: 24px;
        color: var(--text);
    }
    
    .logout-btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        background: var(--danger);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .logout-btn:hover {
        background: #d90429;
    }
    
    .container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        padding: 25px;
        overflow-x: auto;
    }
    
    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
        align-items: flex-end;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text);
        font-size: 14px;
    }
    
    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    
    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: var(--primary);
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .checkbox-group input {
        width: auto;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
        font-size: 14px;
    }
    
    .btn:hover {
        background: var(--primary-dark);
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
    }
    
    th {
        background: #f1f5f9;
        color: var(--text);
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
    }
    
    td {
        padding: 12px 15px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    tr:hover {
        background-color: #f8fafc;
    }
    
    .status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-0 { background: #fff4e5; color: var(--warning); }
    .status-1 { background: #e6f7fd; color: var(--success); }
    .status-2 { background: #fee2e2; color: var(--danger); }
    
    .action-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        color: var(--primary);
        margin: 0 5px;
        padding: 5px;
        border-radius: 4px;
        transition: background 0.2s;
    }
    
    .action-btn:hover {
        background: #f1f5f9;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        padding: 25px;
        border-radius: 12px;
        width: 90%;
        max-width: 700px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .modal-header h2 {
        font-size: 20px;
        color: var(--text);
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: var(--gray);
    }
    
    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .form-group {
        flex: 1;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text);
        font-size: 14px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
    }
    
    .section-title {
        font-size: 16px;
        color: var(--text);
        margin: 20px 0 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    
    @media (max-width: 768px) {
        .filters {
            flex-direction: column;
        }
        
        .filter-group {
            min-width: 100%;
        }
        
        .form-row {
            flex-direction: column;
        }
    }
    </style>
</head>
<body>
    <header class="header">
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