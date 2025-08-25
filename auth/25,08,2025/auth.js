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
    checkLoginAvailability(login);
}

function checkLoginAvailability(login) {
    fetch('check_login.php?login=' + encodeURIComponent(login))
        .then(response => response.json())
        .then(data => {
            const status = document.getElementById('login-status');
            status.textContent = data.available ? '✓ Доступен' : '✗ Занят';
            status.style.color = data.available ? '#16a34a' : '#dc2626';
        });
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

// auth.js - дополните существующий код

function fetchBankDetails() {
    const bik = document.getElementById('bank_bik').value;
    const errorElement = document.getElementById('bik-error');
    const error = {code: 0, message: ''};
    
    if (!validateBik(bik, error)) {
        document.getElementById('bank_bik').style.borderColor = '#dc2626';
        errorElement.textContent = error.message;
        return;
    }
    
    fetch('get_bank.php?bik=' + bik)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('bank_name').value = data.bank.name;
                document.getElementById('bank_ks').value = data.bank.ks;
                document.getElementById('bank_bik').style.borderColor = '';
                errorElement.textContent = '';
            } else {
                document.getElementById('bank_bik').style.borderColor = '#dc2626';
                errorElement.textContent = 'Банк не найден в базе. Проверьте БИК';
                // Автоматическое скрытие через 5 секунд
                setTimeout(() => {
                    errorElement.textContent = '';
                }, 5000);
            }
        });
}
