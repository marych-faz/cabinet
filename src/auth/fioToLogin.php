<?php
function fioToLogin($fullName) {
    $translitMap = [
        'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g',  'д' => 'd',
        'е' => 'e',  'ё' => 'e',  'ж' => 'zh', 'з' => 'z',  'и' => 'i',
        'й' => 'y',  'к' => 'k',  'л' => 'l',  'м' => 'm',  'н' => 'n',
        'о' => 'o',  'п' => 'p',  'р' => 'r',  'с' => 's',  'т' => 't',
        'у' => 'u',  'ф' => 'f',  'х' => 'h',  'ц' => 'c',  'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch','ъ' => '',   'ы' => 'y',  'ь' => '',
        'э' => 'e',  'ю' => 'yu', 'я' => 'ya'
    ];

    function translitChar($char, $map) {
        return $map[mb_strtolower($char)] ?? $char;
    }

    $parts = preg_split('/\s+/', trim($fullName));
    
    if (count($parts) < 2) {
        throw new Exception("Введите как минимум фамилию и имя");
    }

    $surname = $parts[0];
    $name = $parts[1];
    $patronymic = $parts[2] ?? null;

    // Транслитерация фамилии
    $surnameTranslit = '';
    for ($i = 0; $i < mb_strlen($surname); $i++) {
        $char = mb_substr($surname, $i, 1);
        $surnameTranslit .= translitChar($char, $translitMap);
    }

    // Первая буква имени
    $nameInitial = translitChar(mb_substr($name, 0, 1), $translitMap);

    // Первая буква отчества (если есть)
    $patronymicInitial = $patronymic ? translitChar(mb_substr($patronymic, 0, 1), $translitMap) : '';

    $login = $surnameTranslit . ucfirst($nameInitial) . ucfirst($patronymicInitial);
    return $login;
}