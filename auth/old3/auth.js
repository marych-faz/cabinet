// Транслитерация по стандарту fioToLogin.php
function translitChar(char, map) {
    return map[char] || char;
}

function generateLogin() {
    const translitMap = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
        'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i',
        'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
        'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
        'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch',
        'ш': 'sh', 'щ': 'shch', 'ъ': '', 'ы': 'y', 'ь': '',
        'э': 'e', 'ю': 'yu', 'я': 'ya'
    };

    const fullName = document.getElementById('name').value.trim();
    if (!fullName) return;

    const parts = fullName.split(/\s+/).filter(part => part.length > 0);
    if (parts.length < 3) {
        alert("Введите полное ФИО (Фамилия Имя Отчество)");
        return;
    }

    const surname = parts[0];
    const name = parts[1];
    const patronymic = parts[2];

    let login = '';
    
    // Первая буква фамилии (как есть)
    login += translitChar(surname[0], translitMap);
    
    // Остаток фамилии (транслитерация + lowercase)
    for (let i = 1; i < surname.length; i++) {
        login += translitChar(surname[i].toLowerCase(), translitMap);
    }
    
    // Первые буквы имени и отчества (uppercase)
    login += translitChar(name[0], translitMap).toUpperCase();
    login += translitChar(patronymic[0], translitMap).toUpperCase();

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

function refreshCaptcha() {
    document.getElementById('captcha-image').src = 'captcha.php?t=' + Date.now();
}