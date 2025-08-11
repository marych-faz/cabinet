<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// Check authorization
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo->beginTransaction();
        
        switch ($_POST['action']) {
            case 'get_org':
                $stmt = $pdo->prepare("SELECT o.*, u.name as partner_name 
                                      FROM orgs o 
                                      JOIN users u ON o.partner_id = u.id 
                                      WHERE o.id = ?");
                $stmt->execute([$_POST['id']]);
                $org = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($org) {
                    echo json_encode($org);
                } else {
                    echo json_encode(['error' => 'Организация не найдена']);
                }
                break;
                
            case 'update_org':
                $stmt = $pdo->prepare("UPDATE orgs SET 
                    name = ?, inn = ?, kpp = ?, percent = ?
                    WHERE id = ?");
                
                $stmt->execute([
                    $_POST['name'], $_POST['inn'], $_POST['kpp'], $_POST['percent'],
                    $_POST['id']
                ]);
                echo json_encode(['success' => true]);
                break;
                
            case 'archive_org':
                $stmt = $pdo->prepare("UPDATE orgs SET status = 3, is_archived = 1 WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => true]);
                break;
                
            case 'restore_org':
                $stmt = $pdo->prepare("UPDATE orgs SET status = 0, is_archived = 0 WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                echo json_encode(['success' => true]);
                break;
                
            case 'delete_org':
                // Check if org is referenced in act_detail
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM act_detail WHERE org_id = ?");
                $stmt->execute([$_POST['id']]);
                $count = $stmt->fetchColumn();
                
                if ($count > 0) {
                    echo json_encode(['warning' => 'Организация используется в актах. Рекомендуется отправить в архив.']);
                } else {
                    $stmt = $pdo->prepare("DELETE FROM orgs WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    echo json_encode(['success' => true]);
                }
                break;
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
    }
    exit();
}

// Get list of organizations
$showArchived = isset($_GET['archived']) && $_GET['archived'] == '1';
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'name';
$order = $_GET['order'] ?? 'asc';

$query = "SELECT o.id, o.name, o.inn, o.kpp, o.percent, o.status, o.is_archived, u.name as partner_name 
          FROM orgs o 
          JOIN users u ON o.partner_id = u.id";

$where = [];
$params = [];

if (!$showArchived) $where[] = "o.is_archived = 0";
if (!empty($search)) {
    $where[] = "(o.name LIKE ? OR o.inn LIKE ? OR o.kpp LIKE ? OR u.name LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}
if ($statusFilter !== 'all') {
    $where[] = "o.status = ?";
    $params[] = $statusFilter;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY $sort $order";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список организаций</title>
    <link rel="stylesheet" href="orgs.css">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <svg class="header-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4"/>
            </svg>
            <h1>Список организаций</h1>
        </div>
        <button onclick="window.history.back()" class="logout-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Выход
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
                    <option value="3" <?= $statusFilter === '3' ? 'selected' : '' ?>>В архиве</option>
                </select>
            </div>
            <div class="filter-group checkbox-group">
                <input type="checkbox" id="showArchived" <?= $showArchived ? 'checked' : '' ?>>
                <label for="showArchived">Показать архивные</label>
            </div>
            <div class="filter-group">
                <button id="applyFilters" class="btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Применить
                </button>
                <button id="newOrgBtn" class="btn" style="margin-left: 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Новая
                </button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Наименование</th>
                    <th>ИНН</th>
                    <th>КПП</th>
                    <th>Процент</th>
                    <th>Партнер</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orgs as $index => $org): ?>
                <tr class="<?= $index % 2 === 0 ? 'even' : 'odd' ?>">
                    <td><?= htmlspecialchars($org['name']) ?></td>
                    <td><?= htmlspecialchars($org['inn']) ?></td>
                    <td><?= htmlspecialchars($org['kpp'] ?? '') ?></td>
                    <td><?= $org['percent'] ?>%</td>
                    <td><?= htmlspecialchars($org['partner_name']) ?></td>
                    <td><span class="status status-<?= $org['status'] ?>">
                        <?= 
                            $org['status'] == 0 ? 'Черновик' : 
                            ($org['status'] == 1 ? 'На одобрение' : 
                            ($org['status'] == 2 ? 'Одобрено' : 'В архиве')) 
                        ?>
                    </span></td>
                    <td>
                        <button class="action-btn edit-btn" data-id="<?= $org['id'] ?>" title="Редактировать">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <?php if ($org['status'] == 3): ?>
                            <button class="action-btn restore-btn" data-id="<?= $org['id'] ?>" title="Восстановить">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                            </button>
                        <?php else: ?>
                            <button class="action-btn archive-btn" data-id="<?= $org['id'] ?>" title="В архив">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                            </button>
                        <?php endif; ?>
                        <button class="action-btn delete-btn" data-id="<?= $org['id'] ?>" title="Удалить">
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

    <!-- Edit Organization Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Новая организация</h2>
                <button class="modal-close" id="cancelBtn">&times;</button>
            </div>
            
            <form id="orgForm">
                <input type="hidden" id="orgId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">Наименование</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="inn" class="form-label">ИНН</label>
                        <input type="text" id="inn" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="kpp" class="form-label">КПП</label>
                        <input type="text" id="kpp" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="percent" class="form-label">Процент</label>
                        <input type="number" id="percent" class="form-control" min="0" max="100" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Партнер</label>
                        <input type="text" id="partner_name" class="form-control" readonly>
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
                    <button type="button" id="archiveBtn" class="btn" style="background: var(--warning);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        В архив
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
        // Apply filters
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
        
        // New organization
        document.getElementById('newOrgBtn').addEventListener('click', function() {
            document.getElementById('modalTitle').textContent = 'Новая организация';
            document.getElementById('orgId').value = '';
            document.getElementById('name').value = '';
            document.getElementById('inn').value = '';
            document.getElementById('kpp').value = '';
            document.getElementById('percent').value = '10';
            document.getElementById('partner_name').value = '';
            document.getElementById('archiveBtn').style.display = 'none';
            document.getElementById('editModal').style.display = 'flex';
        });
        
        // Edit organization
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const orgId = this.getAttribute('data-id');
                fetch('orgs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_org&id=${orgId}`
                })
                .then(response => response.json())
                .then(org => {
                    if (org.error) throw new Error(org.error);
                    
                    document.getElementById('modalTitle').textContent = 'Редактирование организации';
                    document.getElementById('orgId').value = org.id;
                    document.getElementById('name').value = org.name || '';
                    document.getElementById('inn').value = org.inn || '';
                    document.getElementById('kpp').value = org.kpp || '';
                    document.getElementById('percent').value = org.percent || '';
                    document.getElementById('partner_name').value = org.partner_name || '';
                    
                    // Show/hide archive button based on status
                    if (org.status == 3) {
                        document.getElementById('archiveBtn').style.display = 'none';
                    } else {
                        document.getElementById('archiveBtn').style.display = 'inline-flex';
                    }
                    
                    document.getElementById('editModal').style.display = 'flex';
                })
                .catch(error => {
                    alert('Ошибка загрузки данных: ' + error.message);
                });
            });
        });
        
        // Archive organization
        document.getElementById('archiveBtn').addEventListener('click', function() {
            if (confirm('Вы уверены, что хотите отправить организацию в архив?')) {
                const formData = new URLSearchParams();
                formData.append('action', 'archive_org');
                formData.append('id', document.getElementById('orgId').value);
                
                fetch('orgs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    alert('Организация успешно отправлена в архив');
                    location.reload();
                })
                .catch(error => {
                    alert('Ошибка: ' + error.message);
                });
            }
        });
        
        // Archive from table
        document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите отправить организацию в архив?')) {
                    const formData = new URLSearchParams();
                    formData.append('action', 'archive_org');
                    formData.append('id', this.getAttribute('data-id'));
                    
                    fetch('orgs.php', {
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
        
        // Restore from table
        document.querySelectorAll('.restore-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите восстановить организацию из архива?')) {
                    const formData = new URLSearchParams();
                    formData.append('action', 'restore_org');
                    formData.append('id', this.getAttribute('data-id'));
                    
                    fetch('orgs.php', {
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
        
        // Delete organization
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите удалить организацию?')) {
                    fetch('orgs.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete_org&id=${this.getAttribute('data-id')}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.warning) {
                            if (confirm(data.warning + ' Отправить в архив?')) {
                                const formData = new URLSearchParams();
                                formData.append('action', 'archive_org');
                                formData.append('id', this.getAttribute('data-id'));
                                
                                return fetch('orgs.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: formData
                                });
                            } else {
                                return Promise.reject('Отменено пользователем');
                            }
                        } else if (data.error) {
                            throw new Error(data.error);
                        } else {
                            return data;
                        }
                    })
                    .then(() => location.reload())
                    .catch(error => {
                        if (error !== 'Отменено пользователем') {
                            alert('Ошибка: ' + error);
                        }
                    });
                }
            });
        });
        
        // Save organization
        document.getElementById('saveBtn').addEventListener('click', function() {
            const formData = new URLSearchParams();
            formData.append('action', 'update_org');
            formData.append('id', document.getElementById('orgId').value);
            formData.append('name', document.getElementById('name').value);
            formData.append('inn', document.getElementById('inn').value);
            formData.append('kpp', document.getElementById('kpp').value);
            formData.append('percent', document.getElementById('percent').value);
            
            fetch('orgs.php', {
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
        
        // Close modal
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