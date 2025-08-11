<div class="auth-container">
  <div class="auth-header">
    <div class="logo">
      <svg viewBox="0 0 24 24"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
    </div>
    <h1 class="auth-title">Создать аккаунт</h1>
    <p class="auth-subtitle">Заполните форму регистрации</p>
  </div>
  
  <div class="auth-body">
    <?php if (isset($error)): ?>
      <div class="status-message status-error">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php elseif (isset($success)): ?>
      <div class="status-message status-success">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        <span><?= htmlspecialchars($success) ?></span>
      </div>
    <?php endif; ?>
    
    <form method="post" id="registerForm" class="auth-form">
      <input type="hidden" name="action" value="register">
      
      <div class="form-group">
        <label for="name" class="form-label">ФИО</label>
        <input type="text" id="name" name="name" class="form-control" 
               placeholder="Фамилия Имя Отчество" 
               required 
               onblur="generateLogin()">
      </div>
      
      <div class="form-group">
        <label for="login" class="form-label">Логин</label>
        <input type="text" id="login" name="login" class="form-control" 
               placeholder="Сгенерируется автоматически" 
               required
               readonly>
        <div id="login-status" class="status-hint"></div>
      </div>
      
      <div class="form-group">
        <label for="password" class="form-label">Пароль</label>
        <input type="password" id="password" name="password" class="form-control" 
               placeholder="Не менее 8 символов" 
               required>
      </div>
      
      <div class="form-group">
        <label for="confirm_password" class="form-label">Подтверждение пароля</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
               placeholder="Повторите пароль" 
               required>
      </div>
      
      <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" 
               placeholder="example@domain.com" 
               required>
      </div>
      
      <div class="form-group">
        <label for="phone" class="form-label">Телефон</label>
        <input type="tel" id="phone" name="phone" class="form-control" 
               placeholder="+7 (999) 123-45-67" 
               required>
      </div>
      
      <div class="form-group">
        <label for="dog_num" class="form-label">Номер договора</label>
        <input type="text" id="dog_num" name="dog_num" class="form-control" 
               placeholder="ДГ-2024-001" 
               required>
      </div>
      
      <div class="form-group">
        <label for="dog_beg_date" class="form-label">Дата начала договора</label>
        <input type="date" id="dog_beg_date" name="dog_beg_date" class="form-control" 
               required>
      </div>
      
      <div class="form-group">
        <label for="dog_end_date" class="form-label">Дата окончания договора</label>
        <input type="date" id="dog_end_date" name="dog_end_date" class="form-control" 
               required>
      </div>
      
      <h3 class="section-title">Банковские реквизиты</h3>
      
      <div class="form-group">
        <label for="bank_bik" class="form-label">БИК</label>
        <input type="text" id="bank_bik" name="bank_bik" class="form-control" 
               placeholder="044525999" 
               required
               onblur="fetchBankDetails()">
      </div>
      
      <div class="form-group">
        <label for="bank_name" class="form-label">Наименование банка</label>
        <input type="text" id="bank_name" name="bank_name" class="form-control" 
               placeholder="ПАО 'СБЕРБАНК'" 
               required>
      </div>
      
      <div class="form-group">
        <label for="bank_rs" class="form-label">Расчетный счет</label>
        <input type="text" id="bank_rs" name="bank_rs" class="form-control" 
               placeholder="40702810900000012345" 
               required>
      </div>
      
      <div class="form-group">
        <label for="bank_ks" class="form-label">Корреспондентский счет</label>
        <input type="text" id="bank_ks" name="bank_ks" class="form-control" 
               placeholder="30101810400000000225" 
               required>
      </div>
      
      <div class="captcha-container">
        <img src="captcha.php" id="captcha-image" class="captcha-image">
        <button type="button" class="captcha-refresh" onclick="refreshCaptcha()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
          Обновить
        </button>
      </div>
      
      <div class="form-group">
        <label for="captcha" class="form-label">Введите код с картинки</label>
        <input type="text" id="captcha" name="captcha" class="form-control" 
               placeholder="Введите CAPTCHA" 
               required>
      </div>
      
      <button type="submit" class="btn btn-primary">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="margin-right: 8px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
        Зарегистрироваться
      </button>
      
      <div class="form-footer">
        Уже есть аккаунт? <a href="auth.php" class="form-link">Войти</a>
      </div>
    </form>
  </div>
</div>

<script>
function generateLogin() {
    const translitMap = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
        'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i',
        'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
        'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
        'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch',
        'ш': 'sh', 'щ': 'shch','ъ': '', 'ы': 'y', 'ь': '',
        'э': 'e', 'ю': 'yu', 'я': 'ya',
        'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D',
        'Е': 'E', 'Ё': 'E', 'Ж': 'Zh','З': 'Z', 'И': 'I',
        'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N',
        'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T',
        'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C', 'Ч': 'Ch',
        'Ш': 'Sh', 'Щ': 'Shch','Ъ': '', 'Ы': 'Y', 'Ь': '',
        'Э': 'E', 'Ю': 'Yu', 'Я': 'Ya'
    };

    const fullName = document.getElementById('name').value.trim();
    if (!fullName) return;

    const parts = fullName.split(/\s+/).filter(p => p);
    if (parts.length < 2) {
        alert("Введите как минимум фамилию и имя");
        return;
    }

    const surname = parts[0];
    const name = parts[1];
    const patronymic = parts[2] || null;

    // Транслитерация фамилии
    let login = '';
    for (let i = 0; i < surname.length; i++) {
        const char = surname[i];
        login += translitMap[char] || char;
    }

    // Добавляем инициалы
    login += (translitMap[name[0]] || name[0]).toUpperCase();
    if (patronymic) {
        login += (translitMap[patronymic[0]] || patronymic[0]).toUpperCase();
    }

    document.getElementById('login').value = login;
}

function fetchBankDetails() {
    const bik = document.getElementById('bank_bik').value;
    if (bik.length !== 9) return;

    fetch('get_bank.php?bik=' + bik)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('bank_name').value = data.bank.name;
                document.getElementById('bank_ks').value = data.bank.ks;
            }
        });
}

function refreshCaptcha() {
    document.getElementById('captcha-image').src = 'captcha.php?t=' + Date.now();
}
</script>