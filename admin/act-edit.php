<?php
require_once __DIR__ . '/../includes/db_connect.php';
session_start();

// Инициализация всех переменных
$error = $success = '';
$act = $details = $payments = [];
$total_commission = $total_payments = $remaining_amount = 0;
$actId = $_GET['id'] ?? 0;

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth.php");
    exit();
}

// Загрузка данных акта
try {
    if (!$actId) throw new Exception("Не указан ID акта");

    // Основные данные акта
    $stmt = $pdo->prepare("SELECT a.*, u.name AS partner_name FROM acts a JOIN users u ON a.partner_id = u.id WHERE a.id = ?");
    $stmt->execute([$actId]);
    $act = $stmt->fetch();
    if (!$act) throw new Exception("Акт не найден");

    // Детализация акта
    $stmt = $pdo->prepare("SELECT ad.*, o.name AS org_name FROM act_detail ad LEFT JOIN orgs o ON ad.org_id = o.id WHERE ad.act_id = ? ORDER BY ad.num");
    $stmt->execute([$actId]);
    $details = $stmt->fetchAll();

    // Платежи по акту
    $stmt = $pdo->prepare("SELECT * FROM act_payments WHERE act_id = ? ORDER BY date");
    $stmt->execute([$actId]);
    $payments = $stmt->fetchAll();

    // Расчет сумм
    $total_commission = array_sum(array_column($details, 'commission_amount'));
    $total_payments = array_sum(array_column($payments, 'summa'));
    $remaining_amount = $total_commission - $total_payments;

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Обработка AJAX запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo->beginTransaction();
        
        switch ($_POST['ajax_action']) {
            case 'save_payment':
                $paymentData = [
                    'num' => $_POST['payment_num'] ?? '',
                    'date' => $_POST['payment_date'] ?? '',
                    'summa' => (float)($_POST['amount'] ?? 0),
                    'comment' => $_POST['comment'] ?? '',
                    'id' => $_POST['payment_id'] ?? null,
                    'act_id' => $actId
                ];
                
                if (empty($paymentData['num'])) throw new Exception("Номер платежа обязателен");
                if (empty($paymentData['date'])) throw new Exception("Дата платежа обязательна");
                if ($paymentData['summa'] <= 0) throw new Exception("Сумма должна быть больше 0");
                
                $currentPaymentAmount = 0;
                if ($paymentData['id']) {
                    $stmt = $pdo->prepare("SELECT summa FROM act_payments WHERE id = ?");
                    $stmt->execute([$paymentData['id']]);
                    $currentPaymentAmount = (float)$stmt->fetchColumn();
                }
                
                $availableAmount = $total_commission - ($total_payments - $currentPaymentAmount);
                if ($paymentData['summa'] > $availableAmount) {
                    throw new Exception("Сумма превышает доступный остаток");
                }
                
                if ($paymentData['id']) {
                    $stmt = $pdo->prepare("UPDATE act_payments SET num = ?, date = ?, summa = ?, comment = ? WHERE id = ?");
                    $stmt->execute([
                        $paymentData['num'],
                        $paymentData['date'],
                        $paymentData['summa'],
                        $paymentData['comment'],
                        $paymentData['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO act_payments (act_id, num, date, summa, comment) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $paymentData['act_id'],
                        $paymentData['num'],
                        $paymentData['date'],
                        $paymentData['summa'],
                        $paymentData['comment']
                    ]);
                }
                
                echo json_encode(['success' => true]);
                break;
                
            case 'delete_payment':
                $payment_id = $_POST['payment_id'] ?? 0;
                $stmt = $pdo->prepare("DELETE FROM act_payments WHERE id = ?");
                $stmt->execute([$payment_id]);
                echo json_encode(['success' => true]);
                break;
                
            case 'refresh_payments':
                $stmt = $pdo->prepare("SELECT * FROM act_payments WHERE act_id = ? ORDER BY date");
                $stmt->execute([$actId]);
                $payments = $stmt->fetchAll();
                $total_payments = array_sum(array_column($payments, 'summa'));
                $remaining_amount = $total_commission - $total_payments;
                
                ob_start();
                if (empty($payments)): ?>
                    <tr class="empty-row"><td colspan="5">Нет данных</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $i => $payment): ?>
                    <tr data-id="<?= $payment['id'] ?>" class="<?= $i % 2 === 0 ? 'even-row' : 'odd-row' ?>">
                        <td class="text-center"><?= htmlspecialchars($payment['num']) ?></td>
                        <td class="text-center"><?= date('d.m.Y', strtotime($payment['date'])) ?></td>
                        <td class="text-right"><?= number_format($payment['summa'], 2, '.', ' ') ?></td>
                        <td class="text-left"><?= htmlspecialchars($payment['comment']) ?></td>
                        <td class="text-center">
                            <button type="button" class="action-btn edit-payment-btn" title="Редактировать">✏️</button>
                            <button type="button" class="action-btn delete-payment-btn" title="Удалить">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif;
                $html = ob_get_clean();
                
                echo json_encode([
                    'success' => true,
                    'html' => $html,
                    'total_payments' => number_format($total_payments, 2, '.', ' '),
                    'remaining_amount' => number_format($remaining_amount, 2, '.', ' ')
                ]);
                break;
        }
        
        $pdo->commit();
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}

// Обработка сохранения акта
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_act'])) {
    try {
        $status = $_POST['status'] ?? 0;
        $comment = $_POST['comment'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE acts SET status = ?, comment = ? WHERE id = ?");
        $stmt->execute([$status, $comment, $actId]);
        
        $_SESSION['message'] = "Изменения успешно сохранены";
        header("Location: act-edit.php?id=" . $actId);
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акт №<?= htmlspecialchars($act['num'] ?? '') ?></title>
    <link rel="stylesheet" href="act-edit.css">
    <style>
        .act-header-field.num-field { flex: 0 0 120px; min-width: 120px; }
        .act-header-field.partner-field { flex: 2 1 300px; min-width: 250px; }
        .act-header-field.date-field { flex: 0 0 150px; min-width: 150px; }
        .act-header-field.status-field { flex: 1 1 250px; min-width: 200px; }
        .status-select { width: 100%; min-width: 180px; padding: 6px 10px; }
        .compact-table td { padding: 6px 10px !important; font-size: 0.85rem; }
        .compact-table th { padding: 8px 10px !important; font-size: 0.9rem; }
        @media (max-width: 768px) {
            .act-header-field { min-width: 100% !important; flex: 1 1 100% !important; }
            .status-select { min-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="profile-wrapper">
        <div class="profile-header">
            <h1 class="profile-title">Акт №<?= htmlspecialchars($act['num'] ?? '') ?></h1>
            <p class="profile-subtitle">ID: <?= htmlspecialchars($actId) ?></p>
        </div>
        
        <div class="profile-body">
            <?php if ($error): ?>
                <div class="status-message status-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="status-message status-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <form method="post" class="act-form" id="mainForm">
    <div class="act-header">
        <div class="act-header-row">
            <!-- Номер акта - компактное поле -->
            <div class="act-header-field num-field">
                <span class="act-header-label">Номер:</span>
                <span class="act-header-value"><?= htmlspecialchars($act['num'] ?? '') ?></span>
            </div>
            
            <!-- Партнер - уменьшен на 20% -->
            <div class="act-header-field partner-field">
                <span class="act-header-label">Партнер:</span>
                <span class="act-header-value"><?= htmlspecialchars($act['partner_name'] ?? '') ?></span>
            </div>
            
            <!-- Дата акта - увеличен на 30% -->
            <div class="act-header-field date-field">
                <span class="act-header-label">Дата:</span>
                <span class="act-header-value"><?= date('d.m.Y', strtotime($act['date'] ?? '')) ?></span>
            </div>
            
            <!-- Статус -->
            <div class="act-header-field status-field">
                <span class="act-header-label">Статус:</span>
                <select name="status" class="status-select">
                    <option value="0" <?= ($act['status'] ?? 0) == 0 ? 'selected' : '' ?>>Черновик</option>
                    <option value="1" <?= ($act['status'] ?? 0) == 1 ? 'selected' : '' ?>>На одобрении</option>
                    <option value="2" <?= ($act['status'] ?? 0) == 2 ? 'selected' : '' ?>>Одобрено</option>
                    <option value="3" <?= ($act['status'] ?? 0) == 3 ? 'selected' : '' ?>>Отклонено</option>
                </select>
            </div>
        </div>
    </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Комментарий:</label>
                        <textarea name="comment" class="form-control" rows="3"><?= htmlspecialchars($act['comment'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="save_act" class="btn btn-primary">Сохранить изменения</button>
                    <a href="acts.php" class="btn btn-secondary">Назад к списку</a>
                </div>
            </form>
            
            <div class="form-section">
                <h3 class="section-title">Детализация акта</h3>
                <div class="details-table-container">
                    <table class="details-table compact-table">
                        <thead>
                            <tr>
                                <th class="text-center">№</th>
                                <th class="text-center">Реестр номер</th>
                                <th class="text-center">Дата</th>
                                <th class="text-left">Организация</th>
                                <th class="text-right">Сумма размещения</th>
                                <th class="text-right">Платеж оператора</th>
                                <th class="text-right">Комиссия %</th>
                                <th class="text-right">Сумма комиссии</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($details)): ?>
                                <tr class="empty-row"><td colspan="8">Нет данных</td></tr>
                            <?php else: ?>
                                <?php foreach ($details as $i => $detail): ?>
                                <tr class="<?= $i % 2 === 0 ? 'even-row' : 'odd-row' ?>">
                                    <td class="text-center"><?= $detail['num'] ?></td>
                                    <td class="text-center"><?= htmlspecialchars($detail['registry_number']) ?></td>
                                    <td class="text-center"><?= date('d.m.Y', strtotime($detail['date'])) ?></td>
                                    <td class="text-left"><?= htmlspecialchars($detail['org_name'] ?? 'Не указана') ?></td>
                                    <td class="text-right"><?= number_format($detail['placement_amount'], 2, '.', ' ') ?></td>
                                    <td class="text-right"><?= number_format($detail['operator_payment'], 2, '.', ' ') ?></td>
                                    <td class="text-right"><?= $detail['commission_percentage'] ?>%</td>
                                    <td class="text-right"><?= number_format($detail['commission_amount'], 2, '.', ' ') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="total-label">Итого:</td>
                                <td class="total-amount text-right"><?= number_format(array_sum(array_column($details, 'placement_amount')), 2, '.', ' ') ?></td>
                                <td class="total-amount text-right"><?= number_format(array_sum(array_column($details, 'operator_payment')), 2, '.', ' ') ?></td>
                                <td></td>
                                <td class="total-amount text-right"><?= number_format($total_commission, 2, '.', ' ') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title-container">
                    <h3 class="section-title">Платежи по акту</h3>
                    <button type="button" id="addPaymentBtn" class="btn btn-primary">Добавить платеж</button>
                </div>
                
                <div class="payments-table-container">
                    <table class="payments-table compact-table">
                        <thead>
                            <tr>
                                <th class="text-center">№ платежа</th>
                                <th class="text-center">Дата</th>
                                <th class="text-right">Сумма</th>
                                <th class="text-left">Комментарий</th>
                                <th class="text-center">Действия</th>
                            </tr>
                        </thead>
                        <tbody id="paymentsBody">
                            <?php if (empty($payments)): ?>
                                <tr class="empty-row"><td colspan="5">Нет данных</td></tr>
                            <?php else: ?>
                                <?php foreach ($payments as $i => $payment): ?>
                                <tr data-id="<?= $payment['id'] ?>" class="<?= $i % 2 === 0 ? 'even-row' : 'odd-row' ?>">
                                    <td class="text-center"><?= htmlspecialchars($payment['num']) ?></td>
                                    <td class="text-center"><?= date('d.m.Y', strtotime($payment['date'])) ?></td>
                                    <td class="text-right"><?= number_format($payment['summa'], 2, '.', ' ') ?></td>
                                    <td class="text-left"><?= htmlspecialchars($payment['comment']) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="action-btn edit-payment-btn" title="Редактировать">✏️</button>
                                        <button type="button" class="action-btn delete-payment-btn" title="Удалить">🗑️</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="total-label">Итого оплачено:</td>
                                <td class="total-amount text-right" id="totalPayments"><?= number_format($total_payments, 2, '.', ' ') ?></td>
                                <td class="total-label">Остаток:</td>
                                <td class="total-amount text-right" id="remainingAmount"><?= number_format($remaining_amount, 2, '.', ' ') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="paymentModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 class="modal-title" id="paymentModalTitle">Новый платеж</h2>
                <button class="modal-close" id="modalCloseBtn">&times;</button>
            </div>
            <form id="paymentForm" class="modal-form">
                <input type="hidden" id="payment_id" name="payment_id" value="">
                <input type="hidden" name="ajax_action" value="save_payment">
                <input type="hidden" name="act_id" value="<?= $actId ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="payment_num" class="form-label">№ платежа</label>
                        <input type="text" id="payment_num" name="payment_num" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_date" class="form-label">Дата</label>
                        <input type="date" id="payment_date" name="payment_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_amount" class="form-label">Сумма</label>
                        <input type="number" id="payment_amount" name="amount" class="form-control" step="0.01" min="0.01" required>
                        <small class="form-hint">Доступно: <span id="availableAmount"><?= number_format($remaining_amount, 2, '.', ' ') ?></span></small>
                    </div>
                    <div class="form-group">
                        <label for="payment_comment" class="form-label">Комментарий</label>
                        <textarea id="payment_comment" name="comment" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelPaymentBtn">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('paymentModal');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
        const paymentForm = document.getElementById('paymentForm');
        const paymentsBody = document.getElementById('paymentsBody');
        let currentPaymentAmount = 0;
        
        // Открытие модального окна
        document.getElementById('addPaymentBtn').addEventListener('click', function() {
            document.getElementById('paymentModalTitle').textContent = 'Новый платеж';
            paymentForm.reset();
            document.getElementById('payment_id').value = '';
            document.getElementById('payment_date').valueAsDate = new Date();
            currentPaymentAmount = 0;
            updateAvailableAmount();
            modal.classList.add('active');
        });
        
        function closeModal() { modal.classList.remove('active'); }
        modalCloseBtn.addEventListener('click', closeModal);
        cancelPaymentBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
        
        function updateAvailableAmount() {
            const remaining = parseFloat(document.getElementById('remainingAmount').textContent.replace(/\s/g, ''));
            const available = remaining + currentPaymentAmount;
            document.getElementById('availableAmount').textContent = available.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            document.getElementById('payment_amount').max = available;
        }
        
        function refreshPayments() {
            fetch('act-edit.php?id=<?= $actId ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `ajax_action=refresh_payments&act_id=<?= $actId ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                paymentsBody.innerHTML = data.html;
                document.getElementById('totalPayments').textContent = data.total_payments;
                document.getElementById('remainingAmount').textContent = data.remaining_amount;
                updateAvailableAmount();
            })
            .catch(error => alert('Ошибка: ' + error.message));
        }
        
        // Редактирование платежа
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-payment-btn')) {
                const row = e.target.closest('tr');
                const paymentId = row.getAttribute('data-id');
                const cells = row.cells;
                
                document.getElementById('paymentModalTitle').textContent = 'Редактирование платежа';
                document.getElementById('payment_id').value = paymentId;
                document.getElementById('payment_num').value = cells[0].textContent.trim();
                
                const dateParts = cells[1].textContent.trim().split('.');
                document.getElementById('payment_date').value = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
                
                const amount = cells[2].textContent.trim().replace(/\s/g, '');
                document.getElementById('payment_amount').value = amount;
                currentPaymentAmount = parseFloat(amount);
                
                document.getElementById('payment_comment').value = cells[3].textContent.trim();
                updateAvailableAmount();
                modal.classList.add('active');
            }
            
            // Удаление платежа
            if (e.target.closest('.delete-payment-btn') && confirm('Вы уверены, что хотите удалить этот платеж?')) {
                const paymentId = e.target.closest('tr').getAttribute('data-id');
                fetch('act-edit.php?id=<?= $actId ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ajax_action=delete_payment&payment_id=${paymentId}`
                })
                .then(response => response.json())
                .then(data => data.error ? Promise.reject(data.error) : refreshPayments())
                .catch(error => alert('Ошибка: ' + error));
            }
        });
        
        // Сохранение платежа
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('act-edit.php?id=<?= $actId ?>', {
                method: 'POST',
                body: new FormData(paymentForm)
            })
            .then(response => response.json())
            .then(data => data.error ? Promise.reject(data.error) : (closeModal(), refreshPayments()))
            .catch(error => alert('Ошибка сохранения: ' + error));
        });
        
        refreshPayments();
    });
    </script>
</body>
</html>