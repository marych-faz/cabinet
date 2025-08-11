<?php
/* БЛОК 1: ИНИЦИАЛИЗАЦИЯ И ПОДКЛЮЧЕНИЕ К БД */
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

// Настройка логов
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/../logs/error.log');

/* БЛОК 2: ПОДГОТОВКА ДАННЫХ */
$isNew = isset($_GET['new']);
$actId = $_GET['id'] ?? null;
$act = null;
$details = [];
$partnerName = '';
$error = '';
$success = '';

/* БЛОК 3: ЗАГРУЗКА ДАННЫХ АКТА */
if (!$isNew && $actId) {
    try {
        // Загрузка основного акта
        $stmt = $pdo->prepare("SELECT a.*, u.name AS partner_name FROM acts a JOIN users u ON a.partner_id = u.id WHERE a.id = ? AND a.partner_id = ?");
        $stmt->execute([$actId, $_SESSION['user_id']]);
        $act = $stmt->fetch();
        
        if (!$act) throw new Exception("Акт не найден или нет доступа");
        
        $partnerName = $act['partner_name'];
        
        // Загрузка детализации
        $stmt = $pdo->prepare("SELECT ad.*, o.name AS org_name FROM act_detail ad LEFT JOIN orgs o ON ad.org_id = o.id WHERE ad.act_id = ? ORDER BY ad.num");
        $stmt->execute([$actId]);
        $details = $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Ошибка загрузки акта: " . $e->getMessage());
        $error = $e->getMessage();
    }
} elseif ($isNew) {
    // Для нового акта загружаем только имя партнера
    try {
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $partner = $stmt->fetch();
        $partnerName = $partner['name'] ?? '';
    } catch (Exception $e) {
        error_log("Ошибка загрузки партнера: " . $e->getMessage());
    }
}

/* БЛОК 4: ОБРАБОТКА AJAX ЗАПРОСОВ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    try {
        // Для новых записей сначала проверяем сохранение акта
        $ajaxActId = $_POST['act_id'] ?? $actId;
        
        if (in_array($_POST['ajax_action'], ['get_detail', 'save_detail', 'delete_detail', 'refresh_details']) && !$ajaxActId) {
            throw new Exception("Акт не определен. Сначала сохраните акт.");
        }
        
        $pdo->beginTransaction();
        
        switch ($_POST['ajax_action']) {
            case 'get_detail':
                $stmt = $pdo->prepare("SELECT * FROM act_detail WHERE id = ? AND act_id = ?");
                $stmt->execute([$_POST['id'], $ajaxActId]);
                $detail = $stmt->fetch();
                
                if (!$detail) {
                    throw new Exception("Запись не найдена");
                }
                
                echo json_encode($detail);
                break;
                
            case 'save_detail':
                if (empty($_POST['registry_number'])) throw new Exception("Реестровый номер обязателен");
                
                $orgId = !empty($_POST['org_id']) ? (int)$_POST['org_id'] : null;
                $detailId = $_POST['detail_id'] ?? null;
                
                if ($detailId) {
                    // Обновление существующей записи
                    $stmt = $pdo->prepare("UPDATE act_detail SET 
                        org_id = ?, num = ?, registry_number = ?, date = ?, 
                        placement_amount = ?, operator_payment = ?, 
                        commission_percentage = ?, commission_amount = ?
                        WHERE id = ? AND act_id = ?");
                    $stmt->execute([
                        $orgId, $_POST['num'], $_POST['registry_number'], $_POST['date'],
                        $_POST['placement_amount'], $_POST['operator_payment'],
                        $_POST['commission_percentage'], $_POST['commission_amount'],
                        $detailId, $ajaxActId
                    ]);
                } else {
                    // Создание новой записи                    
                    $stmt = $pdo->prepare("INSERT INTO act_detail 
                        (act_id, org_id, num, registry_number, date, 
                        placement_amount, operator_payment, 
                        commission_percentage, commission_amount) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $ajaxActId, $orgId, $_POST['num'], $_POST['registry_number'], $_POST['date'],
                        $_POST['placement_amount'], $_POST['operator_payment'],
                        $_POST['commission_percentage'], $_POST['commission_amount']
                    ]);
                    $detailId = $pdo->lastInsertId();
                }
                
                echo json_encode(['success' => true, 'id' => $detailId]);
                break;
                
            case 'delete_detail':
                $stmt = $pdo->prepare("DELETE FROM act_detail WHERE id = ? AND act_id = ?");
                $stmt->execute([$_POST['id'], $ajaxActId]);
                
                if ($stmt->rowCount() === 0) {
                    throw new Exception("Запись не найдена или уже удалена");
                }
                
                echo json_encode(['success' => true]);
                break;
                
            case 'refresh_details':
                $stmt = $pdo->prepare("SELECT ad.*, o.name AS org_name FROM act_detail ad LEFT JOIN orgs o ON ad.org_id = o.id WHERE ad.act_id = ? ORDER BY ad.num");
                $stmt->execute([$ajaxActId]);
                $details = $stmt->fetchAll();
                
                ob_start();
                if (empty($details)): ?>
                    <tr class="empty-row">
                        <td colspan="9">Нет данных</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($details as $i => $detail): ?>
                        <tr data-id="<?= $detail['id'] ?>" class="<?= $i % 2 === 0 ? 'even-row' : 'odd-row' ?>">
                            <td class="text-center"><?= $detail['num'] ?></td>
                            <td class="text-center"><?= htmlspecialchars($detail['registry_number']) ?></td>
                            <td class="text-center"><?= date('d-m-Y', strtotime($detail['date'])) ?></td>
                            <td class="text-center"><?= htmlspecialchars($detail['org_name'] ?? 'Не указана') ?></td>
                            <td class="text-right"><?= number_format($detail['placement_amount'], 2, '.', ' ') ?></td>
                            <td class="text-right"><?= number_format($detail['operator_payment'], 2, '.', ' ') ?></td>
                            <td class="text-right"><?= $detail['commission_percentage'] ?>%</td>
                            <td class="text-right"><?= number_format($detail['commission_amount'], 2, '.', ' ') ?></td>
                            <td class="text-center">
                                <button type="button" class="action-btn edit-detail-btn" title="Редактировать">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="action-btn delete-detail-btn" title="Удалить">
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
                <?php endif;
                $html = ob_get_clean();
                
                $totals = [
                    'placement' => number_format(array_sum(array_column($details, 'placement_amount')), 2, '.', ' '),
                    'operator' => number_format(array_sum(array_column($details, 'operator_payment')), 2, '.', ' '),
                    'commission' => number_format(array_sum(array_column($details, 'commission_amount')), 2, '.', ' ')
                ];
                
                echo json_encode(['success' => true, 'html' => $html, 'totals' => $totals]);
                break;
                
            default:
                throw new Exception("Неизвестное действие");
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("AJAX Error: " . $e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

/* БЛОК 5: СОХРАНЕНИЕ ОСНОВНОГО АКТА */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    try {
        $num = trim($_POST['num'] ?? '');
        $date = $_POST['date'] ?? '';
        $status = $_POST['status'] ?? 0;
        $comment = trim($_POST['comment'] ?? '');
        
        if (empty($num)) throw new Exception("Номер акта не может быть пустым");
        if (empty($date)) throw new Exception("Дата акта не может быть пустой");
        
        $pdo->beginTransaction();
        
        if ($isNew) {
            $stmt = $pdo->prepare("INSERT INTO acts (partner_id, num, date, status, comment) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $num, $date, $status, $comment]);
            $actId = $pdo->lastInsertId();
            $success = "Акт успешно создан";
        } else {
            $stmt = $pdo->prepare("UPDATE acts SET num = ?, date = ?, status = ?, comment = ? WHERE id = ? AND partner_id = ?");
            $stmt->execute([$num, $date, $status, $comment, $actId, $_SESSION['user_id']]);
            $success = "Акт успешно обновлен";
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = $success;
        header("Location: acts.php?id=".$actId);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Ошибка сохранения акта: " . $e->getMessage());
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isNew ? 'Новый акт' : 'Редактирование акта' ?></title>
    <link rel="stylesheet" href="act-edit.css">
</head>
<body>
    <div class="profile-wrapper">
        <div class="profile-header">
            <h1 class="profile-title"><?= $isNew ? 'Новый акт' : 'Редактирование акта' ?></h1>
            <?php if (!$isNew): ?>
                <p class="profile-subtitle">ID: <?= htmlspecialchars($actId) ?></p>
            <?php endif; ?>
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
            
            <form method="post" class="profile-form" id="mainForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="act_id" value="<?= htmlspecialchars($actId) ?>">
                
                <div class="form-section">
                    <h3 class="section-title">Основные данные</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Партнер</label>
                            <input type="text" class="form-control readonly-field" value="<?= htmlspecialchars($partnerName) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Номер акта</label>
                            <input type="text" name="num" class="form-control" value="<?= htmlspecialchars($act['num'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Дата акта</label>
                            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($act['date'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Статус</label>
                            <select name="status" class="form-control">
                                <option value="0" <?= ($act['status'] ?? 0) == 0 ? 'selected' : '' ?>>Черновик</option>
                                <option value="1" <?= ($act['status'] ?? 0) == 1 ? 'selected' : '' ?>>На одобрение</option>
                                <option value="2" <?= ($act['status'] ?? 0) == 2 ? 'selected' : '' ?>>Одобрено</option>
                                <option value="3" <?= ($act['status'] ?? 0) == 3 ? 'selected' : '' ?>>Архив</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1">
                            <label class="form-label">Комментарий</label>
                            <textarea name="comment" class="form-control" rows="3"><?= htmlspecialchars($act['comment'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="section-title-container">
                        <h3 class="section-title">Детализация акта</h3>
                        <button type="button" id="addDetailBtn" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Добавить строку
                        </button>
                    </div>
                    
                    <div class="details-table-container">
                        <table class="details-table">
                            <thead>
                                <tr>
                                    <th class="text-center">№</th>
                                    <th class="text-center">Реестр.<br>номер</th>
                                    <th class="text-center">Дата</th>
                                    <th class="text-center">Организация</th>
                                    <th class="text-right">Сумма<br>размещения</th>
                                    <th class="text-right">Платеж<br>оператора</th>
                                    <th class="text-right">Комиссия<br>%</th>
                                    <th class="text-right">Сумма<br>комиссии</th>
                                    <th class="text-center">Действия</th>
                                </tr>
                            </thead>
                            <tbody id="detailsBody">
                                <?php if (empty($details)): ?>
                                    <tr class="empty-row">
                                        <td colspan="9">Нет данных</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($details as $i => $detail): ?>
                                        <tr data-id="<?= $detail['id'] ?>" class="<?= $i % 2 === 0 ? 'even-row' : 'odd-row' ?>">
                                            <td class="text-center"><?= $detail['num'] ?></td>
                                            <td class="text-center"><?= htmlspecialchars($detail['registry_number']) ?></td>
                                            <td class="text-center"><?= date('d-m-Y', strtotime($detail['date'])) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($detail['org_name'] ?? 'Не указана') ?></td>
                                            <td class="text-right"><?= number_format($detail['placement_amount'], 2, '.', ' ') ?></td>
                                            <td class="text-right"><?= number_format($detail['operator_payment'], 2, '.', ' ') ?></td>
                                            <td class="text-right"><?= $detail['commission_percentage'] ?>%</td>
                                            <td class="text-right"><?= number_format($detail['commission_amount'], 2, '.', ' ') ?></td>
                                            <td class="text-center">
                                                <button type="button" class="action-btn edit-detail-btn" title="Редактировать">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </button>
                                                <button type="button" class="action-btn delete-detail-btn" title="Удалить">
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
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="total-label">Итого:</td>
                                    <td class="total-amount text-right" id="totalPlacement"><?= number_format(array_sum(array_column($details, 'placement_amount')), 2, '.', ' ') ?></td>
                                    <td class="total-amount text-right" id="totalOperator"><?= number_format(array_sum(array_column($details, 'operator_payment')), 2, '.', ' ') ?></td>
                                    <td></td>
                                    <td class="total-amount text-right" id="totalCommission"><?= number_format(array_sum(array_column($details, 'commission_amount')), 2, '.', ' ') ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Сохранить акт
                    </button>
                    <a href="acts.php" class="btn btn-secondary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно редактирования детализации -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Новая строка детализации</h2>
                <button class="modal-close">&times;</button>
            </div>
            
            <form id="detailForm">
                <input type="hidden" id="detail_id" name="detail_id" value="">
                <input type="hidden" name="ajax_action" value="save_detail">
                <input type="hidden" id="modal_act_id" name="act_id" value="<?= $actId ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">№ строки</label>
                        <input type="number" id="num" name="num" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Реестровый номер</label>
                        <input type="text" id="registry_number" name="registry_number" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Дата</label>
                        <input type="date" id="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Организация</label>
                        <select id="org_id" name="org_id" class="form-control">
                            <option value="">-- Не выбрана --</option>
                            <?php
                            $stmt = $pdo->prepare("SELECT id, name FROM orgs WHERE partner_id = ? AND is_archived = 0 ORDER BY name");
                            $stmt->execute([$_SESSION['user_id']]);
                            $orgs = $stmt->fetchAll();
                            
                            foreach ($orgs as $org) {
                                echo "<option value=\"{$org['id']}\">" . htmlspecialchars($org['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Сумма размещения</label>
                        <input type="number" id="placement_amount" name="placement_amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Платеж оператора</label>
                        <input type="number" id="operator_payment" name="operator_payment" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Комиссия %</label>
                        <input type="number" id="commission_percentage" name="commission_percentage" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Сумма комиссии</label>
                        <input type="number" id="commission_amount" name="commission_amount" class="form-control" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-secondary modal-close">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('detailModal');
        const detailForm = document.getElementById('detailForm');
        const modalTitle = document.getElementById('modalTitle');
        const detailsBody = document.getElementById('detailsBody');
        const modalActId = document.getElementById('modal_act_id');
        let currentActId = <?= $actId ?: 'null' ?>;
        
        // Обновляем ID акта при изменении
        function updateActId(newId) {
            currentActId = newId;
            modalActId.value = newId;
        }
        
        // Функция для обновления таблицы
        function refreshDetails() {
            if (!currentActId) {
                alert('Сначала сохраните акт');
                return;
            }
            
            fetch('act-edit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ajax_action=refresh_details&act_id=${currentActId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                
                detailsBody.innerHTML = data.html;
                document.getElementById('totalPlacement').textContent = data.totals.placement;
                document.getElementById('totalOperator').textContent = data.totals.operator;
                document.getElementById('totalCommission').textContent = data.totals.commission;
            })
            .catch(error => {
                console.error('Ошибка обновления данных:', error);
                alert('Ошибка при обновлении данных: ' + error.message);
            });
        }

        // Открытие модального окна для новой строки
        document.getElementById('addDetailBtn').addEventListener('click', function() {
            if (!currentActId) {
                alert('Сначала сохраните акт');
                return;
            }
            
            modalTitle.textContent = 'Новая строка детализации';
            detailForm.reset();
            document.getElementById('detail_id').value = '';
            document.getElementById('date').value = '<?= date('Y-m-d') ?>';
            document.getElementById('num').value = detailsBody.querySelectorAll('tr:not(.empty-row)').length + 1;
            modal.style.display = 'flex';
        });
        
        // Открытие модального окна для редактирования строки
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-detail-btn')) {
                const row = e.target.closest('tr');
                const detailId = row.getAttribute('data-id');
                
                if (!currentActId) {
                    alert('Акт не сохранен');
                    return;
                }
                
                fetch('act-edit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax_action=get_detail&id=${detailId}&act_id=${currentActId}`
                })
                .then(response => response.json())
                .then(detail => {
                    if (detail.error) throw new Error(detail.error);
                    
                    modalTitle.textContent = 'Редактирование строки';
                    document.getElementById('detail_id').value = detail.id;
                    document.getElementById('num').value = detail.num;
                    document.getElementById('registry_number').value = detail.registry_number;
                    document.getElementById('date').value = detail.date;
                    document.getElementById('org_id').value = detail.org_id || '';
                    document.getElementById('placement_amount').value = detail.placement_amount;
                    document.getElementById('operator_payment').value = detail.operator_payment;
                    document.getElementById('commission_percentage').value = detail.commission_percentage;
                    document.getElementById('commission_amount').value = detail.commission_amount;
                    
                    modal.style.display = 'flex';
                })
                .catch(error => {
                    alert('Ошибка: ' + error.message);
                });
            }
            
            // Удаление строки
            if (e.target.closest('.delete-detail-btn')) {
                if (!currentActId) {
                    alert('Акт не сохранен');
                    return;
                }
                
                if (confirm('Вы уверены, что хотите удалить эту строку?')) {
                    const row = e.target.closest('tr');
                    const detailId = row.getAttribute('data-id');
                    
                    fetch('act-edit.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `ajax_action=delete_detail&id=${detailId}&act_id=${currentActId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) throw new Error(data.error);
                        refreshDetails();
                    })
                    .catch(error => {
                        alert('Ошибка: ' + error.message);
                    });
                }
            }
        });
        
        // Закрытие модального окна
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Сохранение строки детализации
        detailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!currentActId) {
                alert('Акт не сохранен');
                return;
            }
            
            const formData = new FormData(detailForm);
            
            fetch('act-edit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                
                modal.style.display = 'none';
                refreshDetails();
            })
            .catch(error => {
                alert('Ошибка сохранения: ' + error.message);
            });
        });
        
        // Автоматический расчет суммы комиссии
        document.getElementById('commission_percentage').addEventListener('input', function() {
            const percentage = parseFloat(this.value) || 0;
            const amount = parseFloat(document.getElementById('placement_amount').value) || 0;
            document.getElementById('commission_amount').value = (amount * percentage / 100).toFixed(2);
        });
        
        document.getElementById('placement_amount').addEventListener('input', function() {
            const percentage = parseFloat(document.getElementById('commission_percentage').value) || 0;
            const amount = parseFloat(this.value) || 0;
            document.getElementById('commission_amount').value = (amount * percentage / 100).toFixed(2);
        });
        
        // Обновляем ID акта после сохранения основной формы
        document.getElementById('mainForm').addEventListener('submit', function() {
            // При успешном сохранении страница перезагрузится с новым ID
            // Поэтому здесь ничего не делаем
        });
    });
    </script>
</body>
</html>