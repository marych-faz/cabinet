<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Получение ID партнера
$partner_id = $_GET['id'] ?? 0;
$partner = null;
$contracts = [];
$error = '';
$success = '';

try {
    // Получение данных партнера
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$partner_id]);
    $partner = $stmt->fetch();
    
    if (!$partner) {
        throw new Exception("Партнер не найден");
    }
    
    // Получение договоров партнера
    $stmt = $pdo->prepare("SELECT * FROM user_contracts WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$partner_id]);
    $contracts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'save_partner':
                    // Валидация данных
                    $name = trim($_POST['name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $bank_name = trim($_POST['bank_name'] ?? '');
                    $bank_bik = trim($_POST['bank_bik'] ?? '');
                    $bank_ks = trim($_POST['bank_ks'] ?? '');
                    $bank_rs = trim($_POST['bank_rs'] ?? '');

                    // Проверка обязательных полей
                    if (empty($name) || empty($email)) {
                        throw new Exception("Все обязательные поля должны быть заполнены");
                    }

                    // Обновление данных
                    $stmt = $pdo->prepare("UPDATE users SET 
                        name = ?, email = ?, phone = ?,
                        bank_name = ?, bank_bik = ?, bank_ks = ?, bank_rs = ?
                        WHERE id = ?");
                    
                    $stmt->execute([
                        $name, $email, $phone,
                        $bank_name, $bank_bik, $bank_ks, $bank_rs,
                        $partner_id
                    ]);

                    $success = "Данные партнера успешно сохранены";
                    
                    // Обновляем данные партнера
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$partner_id]);
                    $partner = $stmt->fetch();
                    break;
                    
                case 'save_contract':
                    $contract_id = $_POST['contract_id'] ?? 0;
                    $number = trim($_POST['number'] ?? '');
                    $start_date = trim($_POST['start_date'] ?? '');
                    $end_date = trim($_POST['end_date'] ?? '');
                    $status = $_POST['status'] ?? 1;
                    $comment = trim($_POST['comment'] ?? '');
                    
                    if (empty($number) || empty($start_date)) {
                        throw new Exception("Номер договора и дата начала обязательны для заполнения");
                    }
                    
                    if ($contract_id > 0) {
                        // Обновление договора
                        $stmt = $pdo->prepare("UPDATE user_contracts SET 
                            number = ?, start_date = ?, end_date = ?, status = ?, comment = ?
                            WHERE id = ? AND user_id = ?");
                        
                        $stmt->execute([$number, $start_date, $end_date, $status, $comment, $contract_id, $partner_id]);
                        $success = "Договор успешно обновлен";
                    } else {
                        // Создание нового договора
                        $stmt = $pdo->prepare("INSERT INTO user_contracts 
                            (user_id, number, start_date, end_date, status, comment) 
                            VALUES (?, ?, ?, ?, ?, ?)");
                        
                        $stmt->execute([$partner_id, $number, $start_date, $end_date, $status, $comment]);
                        $success = "Договор успешно создан";
                    }
                    
                    // Обновляем список договоров
                    $stmt = $pdo->prepare("SELECT * FROM user_contracts WHERE user_id = ? ORDER BY start_date DESC");
                    $stmt->execute([$partner_id]);
                    $contracts = $stmt->fetchAll();
                    break;
                    
                case 'delete_contract':
                    $contract_id = $_POST['contract_id'] ?? 0;
                    
                    if ($contract_id > 0) {
                        $stmt = $pdo->prepare("DELETE FROM user_contracts WHERE id = ? AND user_id = ?");
                        $stmt->execute([$contract_id, $partner_id]);
                        $success = "Договор успешно удален";
                        
                        // Обновляем список договоров
                        $stmt = $pdo->prepare("SELECT * FROM user_contracts WHERE user_id = ? ORDER BY start_date DESC");
                        $stmt->execute([$partner_id]);
                        $contracts = $stmt->fetchAll();
                    }
                    break;
                    
                case 'change_contract_status':
                    $contract_id = $_POST['contract_id'] ?? 0;
                    $new_status = $_POST['status'] ?? 1;
                    
                    if ($contract_id > 0) {
                        $stmt = $pdo->prepare("UPDATE user_contracts SET status = ? WHERE id = ? AND user_id = ?");
                        $stmt->execute([$new_status, $contract_id, $partner_id]);
                        $success = "Статус договора изменен";
                        
                        // Обновляем список договоров
                        $stmt = $pdo->prepare("SELECT * FROM user_contracts WHERE user_id = ? ORDER BY start_date DESC");
                        $stmt->execute([$partner_id]);
                        $contracts = $stmt->fetchAll();
                    }
                    break;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Функция для получения текстового статуса договора
function getContractStatusText($status) {
    $statuses = [
        1 => 'Черновик',
        2 => 'Активный',
        3 => 'Просроченный',
        4 => 'Прекращенный'
    ];
    return $statuses[$status] ?? 'Неизвестно';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование партнера</title>
    <link rel="stylesheet" href="partner-edit.css">
</head>
<body>
    <div class="profile-wrapper">
        <div class="profile-header">
            <div class="header-left">
                <a href="partners.php" class="details-icon" title="Назад к списку партнеров">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </a>
            </div>
            <div class="header-center">
                <h1 class="profile-title">Редактирование партнера</h1>
                <p class="profile-subtitle"><?= htmlspecialchars($partner['name'] ?? '') ?></p>
            </div>
            <div class="header-right"></div>
        </div>
        
        <div class="profile-body">
            <?php if ($error): ?>
                <div class="status-message status-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="status-message status-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($partner): ?>
            <form method="post" class="profile-form" id="partnerForm">
                <input type="hidden" name="action" value="save_partner">
                
                <div class="form-section">
                    <h3 class="section-title">Основные данные</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ФИО</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= htmlspecialchars($partner['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($partner['email']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Телефон</label>
                            <div class="phone-input-container">
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?= htmlspecialchars($partner['phone']) ?>">
                                <?php if (!empty($partner['phone'])): ?>
                                <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $partner['phone'])) ?>" class="phone-call">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Логин</label>
                            <input type="text" class="form-control readonly-field" 
                                   value="<?= htmlspecialchars($partner['login']) ?>" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Банковские реквизиты</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">БИК</label>
                            <input type="text" name="bank_bik" class="form-control" 
                                   value="<?= htmlspecialchars($partner['bank_bik']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Наименование банка</label>
                            <input type="text" name="bank_name" class="form-control" 
                                   value="<?= htmlspecialchars($partner['bank_name']) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Расчетный счет</label>
                            <input type="text" name="bank_rs" class="form-control" 
                                   value="<?= htmlspecialchars($partner['bank_rs']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Корреспондентский счет</label>
                            <input type="text" name="bank_ks" class="form-control" 
                                   value="<?= htmlspecialchars($partner['bank_ks']) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Договоры партнера</h3>
                    
                    <?php if (!empty($contracts)): ?>
                    <table class="contracts-table">
                        <thead>
                            <tr>
                                <th>№ договора</th>
                                <th>Дата начала</th>
                                <th>Дата окончания</th>
                                <th>Статус</th>
                                <th>Комментарий</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $contract): 
                                $isExpired = strtotime($contract['end_date']) < time();
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($contract['number']) ?></td>
                                <td><?= date('d.m.Y', strtotime($contract['start_date'])) ?></td>
                                <td <?= $isExpired ? 'style="color: var(--danger); font-weight: bold;"' : '' ?>>
                                    <?= date('d.m.Y', strtotime($contract['end_date'])) ?>
                                </td>
                                <td>
                                    <form method="post" class="status-form">
                                        <input type="hidden" name="action" value="change_contract_status">
                                        <input type="hidden" name="contract_id" value="<?= $contract['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="status-select status-<?= $contract['status'] ?>">
                                            <option value="1" <?= $contract['status'] == 1 ? 'selected' : '' ?>>Черновик</option>
                                            <option value="2" <?= $contract['status'] == 2 ? 'selected' : '' ?>>Активный</option>
                                            <option value="3" <?= $contract['status'] == 3 ? 'selected' : '' ?>>Просроченный</option>
                                            <option value="4" <?= $contract['status'] == 4 ? 'selected' : '' ?>>Прекращенный</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?= htmlspecialchars($contract['comment'] ?? '—') ?></td>
                                <td class="contract-actions">
                                    <button type="button" class="contract-btn edit-contract-btn" 
                                            data-id="<?= $contract['id'] ?>" 
                                            data-number="<?= htmlspecialchars($contract['number']) ?>" 
                                            data-start_date="<?= date('Y-m-d', strtotime($contract['start_date'])) ?>" 
                                            data-end_date="<?= date('Y-m-d', strtotime($contract['end_date'])) ?>" 
                                            data-status="<?= $contract['status'] ?>" 
                                            data-comment="<?= htmlspecialchars($contract['comment'] ?? '') ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_contract">
                                        <input type="hidden" name="contract_id" value="<?= $contract['id'] ?>">
                                        <button type="submit" class="contract-btn" onclick="return confirm('Вы уверены, что хотите удалить этот договор?')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p>Договоры не найдены</p>
                    <?php endif; ?>
                    
                    <button type="button" class="btn btn-primary add-contract-btn" id="addContractBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Добавить договор
                    </button>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Сохранить
                    </button>
                    <a href="partners.php" class="btn btn-secondary" id="closeButton">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Закрыть
                    </a>
                </div>
            </form>
            <?php else: ?>
                <div class="status-message status-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>Не удалось загрузить данные партнера</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Модальное окно для редактирования/добавления договора -->
    <div class="modal" id="contractModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="contractModalTitle">Добавление договора</h2>
                <button class="modal-close" id="closeContractModal">&times;</button>
            </div>
            
            <form method="post" id="contractForm">
                <input type="hidden" name="action" value="save_contract">
                <input type="hidden" name="contract_id" id="contract_id" value="0">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="number" class="form-label">№ договора</label>
                        <input type="text" id="number" name="number" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date" class="form-label">Дата начала</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date" class="form-label">Дата окончания</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status" class="form-label">Статус</label>
                        <select id="status" name="status" class="form-control">
                            <option value="1">Черновик</option>
                            <option value="2" selected>Активный</option>
                            <option value="3">Просроченный</option>
                            <option value="4">Прекращенный</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="comment" class="form-label">Комментарий</label>
                        <textarea id="comment" name="comment" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-secondary" id="cancelContractBtn">Отмена</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const contractModal = document.getElementById('contractModal');
        const contractForm = document.getElementById('contractForm');
        const contractModalTitle = document.getElementById('contractModalTitle');
        const contractIdInput = document.getElementById('contract_id');
        const numberInput = document.getElementById('number');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const statusSelect = document.getElementById('status');
        const commentInput = document.getElementById('comment');
        
        // Открытие модального окна для добавления договора
        document.getElementById('addContractBtn').addEventListener('click', function() {
            contractModalTitle.textContent = 'Добавление договора';
            contractIdInput.value = '0';
            numberInput.value = '';
            startDateInput.value = '';
            endDateInput.value = '';
            statusSelect.value = '2';
            commentInput.value = '';
            contractModal.style.display = 'flex';
        });
        
        // Открытие модального окна для редактирования договора
        document.querySelectorAll('.edit-contract-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                contractModalTitle.textContent = 'Редактирование договора';
                contractIdInput.value = this.getAttribute('data-id');
                numberInput.value = this.getAttribute('data-number');
                startDateInput.value = this.getAttribute('data-start_date');
                endDateInput.value = this.getAttribute('data-end_date');
                statusSelect.value = this.getAttribute('data-status');
                commentInput.value = this.getAttribute('data-comment');
                contractModal.style.display = 'flex';
            });
        });
        
        // Закрытие модального окна
        function closeContractModal() {
            contractModal.style.display = 'none';
        }
        
        document.getElementById('closeContractModal').addEventListener('click', closeContractModal);
        document.getElementById('cancelContractBtn').addEventListener('click', closeContractModal);
        
        contractModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeContractModal();
            }
        });
        
        // Проверка изменений перед закрытием
        const form = document.getElementById('partnerForm');
        const closeButton = document.getElementById('closeButton');
        let formChanged = false;

        // Сохраняем исходные значения формы
        const originalValues = {};
        Array.from(form.elements).forEach(element => {
            if (element.name) {
                originalValues[element.name] = element.value;
            }
        });

        // Отслеживаем изменения в форме
        form.addEventListener('input', function() {
            formChanged = Array.from(form.elements).some(element => {
                return element.name && element.value !== originalValues[element.name];
            });
        });

        // Обработка кнопки "Закрыть"
        closeButton.addEventListener('click', function(e) {
            if (formChanged) {
                e.preventDefault();
                const confirmClose = confirm('У вас есть несохраненные изменения. Вы уверены, что хотите закрыть без сохранения?');
                if (confirmClose) {
                    window.location.href = this.href;
                }
            }
        });
    });
    </script>
</body>
</html>