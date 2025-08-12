<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Получение данных компании
$company = null;
$error = '';
$success = '';
$originalData = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM company_details WHERE id = 1");
    $stmt->execute();
    $company = $stmt->fetch();
    
    if (!$company) {
        throw new Exception("Данные компании не найдены");
    }
    
    // Сохраняем оригинальные данные для проверки изменений
    $originalData = $company;
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save') {
        try {
            // Валидация данных
            $name = trim($_POST['name'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $inn = trim($_POST['inn'] ?? '');
            $kpp = trim($_POST['kpp'] ?? '');
            $ogrn = trim($_POST['ogrn'] ?? '');
            $regist_address = trim($_POST['regist_address'] ?? '');
            $e_mail = trim($_POST['e_mail'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $bank_bic = trim($_POST['bank_bic'] ?? '');
            $bank_name = trim($_POST['bank_name'] ?? '');
            $bank_ks = trim($_POST['bank_ks'] ?? '');
            $bank_rs = trim($_POST['bank_rs'] ?? '');

            // Проверка обязательных полей
            if (empty($name) || empty($full_name) || empty($inn) || empty($kpp) || empty($ogrn)) {
                throw new Exception("Все обязательные поля должны быть заполнены");
            }

            // Обновление данных
            $stmt = $pdo->prepare("UPDATE company_details SET 
                name = ?, 
                full_name = ?, 
                inn = ?, 
                kpp = ?, 
                ogrn = ?, 
                regist_address = ?, 
                e_mail = ?, 
                phone = ?, 
                bank_bic = ?, 
                bank_name = ?, 
                bank_ks = ?, 
                bank_rs = ? 
                WHERE id = 1");
            
            $stmt->execute([
                $name,
                $full_name,
                $inn,
                $kpp,
                $ogrn,
                $regist_address,
                $e_mail,
                $phone,
                $bank_bic,
                $bank_name,
                $bank_ks,
                $bank_rs
            ]);

            $success = "Данные компании успешно сохранены";
            
            // Обновляем данные компании
            $stmt = $pdo->prepare("SELECT * FROM company_details WHERE id = 1");
            $stmt->execute();
            $company = $stmt->fetch();
            $originalData = $company;

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реквизиты компании</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="company_details.css">
</head>
<body>
    <div class="profile-wrapper">
        <div class="profile-header">
            <div class="header-left">
                <a href="admin-form.html" class="details-icon" title="Реквизиты организации">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"></path>
                        <path d="M12 12h8"></path>
                        <path d="M12 16h8"></path>
                        <path d="M12 8h8"></path>
                        <path d="M16 20h2a2 2 0 0 0 2-2v-2"></path>
                    </svg>
                </a>
            </div>
            <div class="header-center">
                <h1 class="profile-title">Реквизиты компании</h1>
                <p class="profile-subtitle">Редактирование данных</p>
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
            
            <?php if ($company): ?>
            <form method="post" class="profile-form" id="companyDetailsForm">
                <input type="hidden" name="action" value="save">
                
                <div class="form-section">
                    <h3 class="section-title">Основные реквизиты</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Наименование</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= htmlspecialchars($company['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Полное наименование</label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?= htmlspecialchars($company['full_name']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ИНН</label>
                            <input type="text" name="inn" class="form-control" 
                                   value="<?= htmlspecialchars($company['inn']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">КПП</label>
                            <input type="text" name="kpp" class="form-control" 
                                   value="<?= htmlspecialchars($company['kpp']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">ОГРН</label>
                            <input type="text" name="ogrn" class="form-control" 
                                   value="<?= htmlspecialchars($company['ogrn']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Адрес регистрации</label>
                            <input type="text" name="regist_address" class="form-control" 
                                   value="<?= htmlspecialchars($company['regist_address']) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Контактные данные</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="e_mail" class="form-control" 
                                   value="<?= htmlspecialchars($company['e_mail']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Телефон</label>
                            <div class="phone-input-container">
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?= htmlspecialchars($company['phone']) ?>">
                                <?php if (!empty($company['phone'])): ?>
                                <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $company['phone'])) ?>" class="phone-call">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Банковские реквизиты</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">БИК</label>
                            <input type="text" name="bank_bic" class="form-control" 
                                   value="<?= htmlspecialchars($company['bank_bic']) ?>" required
                                   onblur="fetchBankDetails()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Наименование банка</label>
                            <input type="text" name="bank_name" class="form-control" 
                                   value="<?= htmlspecialchars($company['bank_name']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Расчетный счет</label>
                            <input type="text" name="bank_rs" class="form-control" 
                                   value="<?= htmlspecialchars($company['bank_rs']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Корреспондентский счет</label>
                            <input type="text" name="bank_ks" class="form-control" 
                                   value="<?= htmlspecialchars($company['bank_ks']) ?>" required>
                        </div>
                    </div>
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
                    <a href="admin-form.html" class="btn btn-secondary" id="closeButton">
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
                    <span>Не удалось загрузить данные компании</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    // Функция для получения данных банка по БИК
    function fetchBankDetails() {
        const bik = document.querySelector('input[name="bank_bic"]').value;
        if (bik.length === 9) {
            fetch('get_bank_details.php?bik=' + bik)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('input[name="bank_name"]').value = data.name;
                        document.querySelector('input[name="bank_ks"]').value = data.ks;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }

    // Проверка изменений перед закрытием
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('companyDetailsForm');
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