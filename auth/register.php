<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    // ... (ваш существующий код обработки регистрации) ...
}

$show_register = true;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="auth.css">
    <script src="auth.js"></script>
    <script src="is_valid.js"></script>
</head>
<body>
    <div class="container">
        <div class="auth-container register">
            <div class="auth-header">
                <div class="logo" style="float: left; margin-right: 15px;">
                    <svg viewBox="0 0 24 24"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h1 class="auth-title">Создать аккаунт</h1>
                <p class="auth-subtitle">Выберите ваш статус и заполните данные</p>
            </div>
            
            <div class="auth-body register">
                <?php if (isset($error)): ?>
                    <div class="status-message status-error">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="post" id="registerForm" class="auth-form">
                    <input type="hidden" name="action" value="register">
                    
                    <!-- ШАГ 1: ВЫБОР СТАТУСА -->
                    <div id="step-status" class="form-step">
                        <h3 class="section-title" style="color: var(--primary);">Кто вы?</h3>
                        <div class="form-group">
                            <div class="status-options">
                                <label class="status-option">
                                    <input type="radio" name="agent_status" value="1" required onchange="loadPartialForm(this.value)">
                                    <div class="option-card">
                                        <h4>Физическое лицо</h4>
                                        <p>Работаете как самозанятый или нанимаетесь по договору ГПХ</p>
                                    </div>
                                </label>
                                <label class="status-option">
                                    <input type="radio" name="agent_status" value="2" onchange="loadPartialForm(this.value)">
                                    <div class="option-card">
                                        <h4>Индивидуальный предприниматель (ИП)</h4>
                                        <p>Зарегистрированы в качестве ИП</p>
                                    </div>
                                </label>
                                <label class="status-option">
                                    <input type="radio" name="agent_status" value="3" onchange="loadPartialForm(this.value)">
                                    <div class="option-card">
                                        <h4>Юридическое лицо</h4>
                                        <p>ООО, АО и другие организационно-правовые формы</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ШАГ 2: ОБЩИЕ ДАННЫЕ (всегда видны после выбора статуса) -->
                    <div id="step-common-data" style="display: none;">
                        <h3 class="section-title" style="color: var(--primary);">Общие данные</h3>
                        
                        <!-- ФИО и Логин - в две колонки -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <span id="name-label">ФИО</span> *
                                </label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       placeholder="Введите полное наименование" 
                                       required onblur="generateLogin()">
                            </div>
                            
                            <div class="form-group">
                                <label for="login" class="form-label">Логин *</label>
                                <input type="text" id="login" name="login" class="form-control" 
                                       placeholder="Сгенерируется автоматически" 
                                       required readonly>
                                <div id="login-status" class="status-hint"></div>
                            </div>
                        </div>

                        <!-- Email и Телефон - в две колонки -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       placeholder="example@domain.com" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Телефон *</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       placeholder="+7 (999) 123-45-67" 
                                       required>
                            </div>
                        </div>

                        <!-- Пароль и подтверждение - в две колонки -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password" class="form-label">Пароль *</label>
                                <input type="password" id="password" name="password" class="form-control" 
                                       placeholder="Только латинские буквы и цифры" 
                                       pattern="[A-Za-z0-9@$!%*?&]{8,}" 
                                       required oninput="validatePassword()">
                                <div class="password-hint">Только английские буквы, цифры и специальные символы @$!%*?&</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Подтверждение пароля *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       placeholder="Повторите пароль" 
                                       required oninput="validatePasswordMatch()">
                                <div class="validation-error" id="password-match-error"></div>
                            </div>
                        </div>
                        
                        <!-- Адрес и форма налогообложения - в две колонки -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="address" class="form-label">Адрес регистрации *</label>
                                <input type="text" id="address" name="address" class="form-control" 
                                       placeholder="Полный адрес регистрации" 
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="tax_form" class="form-label">Форма налогообложения *</label>
                                <select id="tax_form" name="tax_form" class="form-control" required>
                                    <option value="">-- Выберите форму налогообложения --</option>
                                    <option value="ОСН">Общая система налогообложения (ОСН)</option>
                                    <option value="УСН">Упрощенная система налогообложения (УСН)</option>
                                    <option value="УСН-доходы">УСН "Доходы"</option>
                                    <option value="УСН-доходы-расходы">УСН "Доходы минус расходы"</option>
                                    <option value="ЕНВД">Единый налог на вмененный доход (ЕНВД)</option>
                                    <option value="ЕСХН">Единый сельскохозяйственный налог (ЕСХН)</option>
                                    <option value="Патент">Патентная система налогообложения</option>
                                    <option value="НПД">Налог на профессиональный доход (самозанятость)</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- ДИНАМИЧЕСКАЯ ФОРМА (загружается из partials) -->
                        <div id="step-dynamic-form"></div>
                        
                        <h3 class="section-title" style="color: var(--primary);">Банковские реквизиты</h3>
                        
                        <!-- БИК и Наименование банка - в две колонки -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="bank_bik" class="form-label">БИК *</label>
                                <input type="text" id="bank_bik" name="bank_bik" class="form-control" 
                                       placeholder="044525999" pattern="\d{9}" maxlength="9" 
                                       required onblur="fetchBankDetails()">
                                <div class="validation-error" id="bik-error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="bank_name" class="form-label">Наименование банка *</label>
                                <input type="text" id="bank_name" name="bank_name" class="form-control" 
                                       placeholder="ПАО 'СБЕРБАНК'" 
                                       required readonly>
                            </div>
                        </div>

                        <!-- Расчетный счет и Корреспондентский счет - в две колонки -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="bank_rs" class="form-label">Расчетный счет *</label>
                                <input type="text" id="bank_rs" name="bank_rs" class="form-control" 
                                       placeholder="40702810900000012345" pattern="\d{20}" maxlength="20" 
                                       required onblur="validateBankRs()">
                                <div class="validation-error" id="rs-error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="bank_ks" class="form-label">Корреспондентский счет *</label>
                                <input type="text" id="bank_ks" name="bank_ks" class="form-control" 
                                       placeholder="30101810400000000225" pattern="\d{20}" maxlength="20" 
                                       required readonly>
                            </div>
                        </div>

                        <!-- ИНН Банка - одна колонка -->
                        <div class="form-group">
                            <label for="bank_inn" class="form-label">ИНН Банка</label>
                            <input type="text" id="bank_inn" name="bank_inn" class="form-control" 
                                   placeholder="773601001" pattern="\d{10}" maxlength="10">
                        </div>
                        
                        <!-- CAPTCHA в одну строку -->
                        <h3 class="section-title" style="color: var(--primary);">Защита от роботов</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <div class="captcha-container">
                                    <img src="captcha.php" id="captcha-image" class="captcha-image">
                                    <button type="button" class="captcha-refresh" onclick="refreshCaptcha()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <polyline points="23 4 23 10 17 10"></polyline>
                                            <polyline points="1 20 1 14 7 14"></polyline>
                                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                        </svg>
                                        Обновить
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="captcha" class="form-label">Введите код с картинки *</label>
                                <input type="text" id="captcha" name="captcha" class="form-control" 
                                       placeholder="Введите CAPTCHA" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="privacy-consent">
                            <input type="checkbox" id="privacy_consent" name="privacy_consent" value="1" required>
                            <label for="privacy_consent">Я соглашаюсь с обработкой персональных данных и принимаю условия 
                            <a href="/privacy-policy.php" target="_blank">Политики конфиденциальности</a></label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Зарегистрироваться
                        </button>
                    </div>
                    
                    <div class="form-footer">
                        Уже есть аккаунт? <a href="auth.php" class="form-link">Войти</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Функции для валидации
    function validateInn(input) {
        const errorElement = document.getElementById(input.id + '-error');
        if (!is_valid_inn(input.value)) {
            input.style.borderColor = '#dc2626';
            errorElement.textContent = 'Неверный ИНН';
            return false;
        } else {
            input.style.borderColor = '';
            errorElement.textContent = '';
            return true;
        }
    }

    function validateOgrn(input) {
        const errorElement = document.getElementById(input.id + '-error');
        if (!is_valid_ogrn(input.value)) {
            input.style.borderColor = '#dc2626';
            errorElement.textContent = 'Неверный ОГРН';
            return false;
        } else {
            input.style.borderColor = '';
            errorElement.textContent = '';
            return true;
        }
    }

    function validateKpp(input) {
        const errorElement = document.getElementById(input.id + '-error');
        if (!/^\d{9}$/.test(input.value)) {
            input.style.borderColor = '#dc2626';
            errorElement.textContent = 'КПП должен содержать 9 цифр';
            return false;
        } else {
            input.style.borderColor = '';
            errorElement.textContent = '';
            return true;
        }
    }

    function validateBankRs() {
        const bik = document.getElementById('bank_bik').value;
        const rs = document.getElementById('bank_rs').value;
        const errorElement = document.getElementById('rs-error');
        const error = {code: 0, message: ''};
        
        if (!validateRs(rs, bik, error)) {
            document.getElementById('bank_rs').style.borderColor = '#dc2626';
            errorElement.textContent = error.message;
            return false;
        } else {
            document.getElementById('bank_rs').style.borderColor = '';
            errorElement.textContent = '';
            return true;
        }
    }

    // Валидация пароля (только английские символы)
    function validatePassword() {
        const password = document.getElementById('password');
        const pattern = /^[A-Za-z0-9@$!%*?&]+$/;
        
        if (!pattern.test(password.value)) {
            password.style.borderColor = '#dc2626';
            return false;
        } else {
            password.style.borderColor = '';
            return true;
        }
    }

    // Проверка совпадения паролей
    function validatePasswordMatch() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const errorElement = document.getElementById('password-match-error');
        
        if (password.value !== confirmPassword.value) {
            confirmPassword.style.borderColor = '#dc2626';
            errorElement.textContent = 'Пароли не совпадают';
            return false;
        } else {
            confirmPassword.style.borderColor = '';
            errorElement.textContent = '';
            return true;
        }
    }

    // Загрузка частичной формы
    function loadPartialForm(agentStatus) {
        document.getElementById('step-common-data').style.display = 'block';
        
        // Обновляем label для name в зависимости от статуса
        const nameLabel = document.getElementById('name-label');
        if (agentStatus == 3) {
            nameLabel.textContent = 'Наименование организации';
            document.getElementById('name').placeholder = 'Полное наименование организации';
        } else {
            nameLabel.textContent = 'ФИО';
            document.getElementById('name').placeholder = 'Фамилия Имя Отчество';
        }
        
        fetch('partials/register-' + getStatusKey(agentStatus) + '.php')
            .then(response => response.text())
            .then(html => {
                document.getElementById('step-dynamic-form').innerHTML = html;
                if (document.getElementById('name').value) {
                    generateLogin();
                }
            });
    }

    function getStatusKey(statusValue) {
        const statusMap = { '1': 'fl', '2': 'ip', '3': 'ul' };
        return statusMap[statusValue] || 'fl';
    }
    </script>
</body>
</html>