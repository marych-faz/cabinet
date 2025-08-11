function fioToLogin(fullName) {
    // Таблица транслита
    const translitMap = {
        'а': 'a',  'б': 'b',  'в': 'v',  'г': 'g',  'д': 'd',
        'е': 'e',  'ё': 'e',  'ж': 'zh', 'з': 'z',  'и': 'i',
        'й': 'y',  'к': 'k',  'л': 'l',  'м': 'm',  'н': 'n',
        'о': 'o',  'п': 'p',  'р': 'r',  'с': 's',  'т': 't',
        'у': 'u',  'ф': 'f',  'х': 'h',  'ц': 'c',  'ч': 'ch',
        'ш': 'sh', 'щ': 'shch','ъ': '',   'ы': 'y',  'ь': '',
        'э': 'e',  'ю': 'yu', 'я': 'ya'
    };

    function translitChar(char) {
        return translitMap[char.toLowerCase()] || char;
    }

    // Разделение на части
    const parts = fullName.trim().split(/\s+/);

    if (parts.length < 2) {
        throw new Error("Введите как минимум фамилию и имя");
    }

    const surname = parts[0];               // всегда фамилия
    const name = parts[1];                  // первое слово после фамилии — имя
    const patronymic = parts[2] || null;    // отчество — опционально

    // Транслитерация фамилии полностью
    const surnameTranslit = surname.split('').map(char => translitChar(char)).join('');
    
    // Первая буква имени
    const nameInitial = translitChar(name[0]).toUpperCase();

    // Первая буква отчества (если есть)
    const patronymicInitial = patronymic ? translitChar(patronymic[0]).toUpperCase() : '';

    // Собираем логин
    const login = surnameTranslit + nameInitial + patronymicInitial;

    // Делаем первую букву заглавной
    return login.charAt(0).toUpperCase() + login.slice(1);
}

// Примеры использования:
console.log(fioToLogin("Фазуллин Марат"));                   // FazullinM
console.log(fioToLogin("Фазуллин Марат Ильевич"));           // FazullinMI
console.log(fioToLogin("Мухамедеев Махмуд Оглы Ибн Хаттаб")); // MukhamedeevMO