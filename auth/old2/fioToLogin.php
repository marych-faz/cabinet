<?php
function fioToLogin($fullName) {
    $translitMap = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];

    $parts = preg_split('/\s+/', trim($fullName));
    if (count($parts) < 2) {
        throw new Exception("Введите Фамилию и Имя");
    }

    $surname = $parts[0];
    $name = $parts[1];
    $patronymic = $parts[2] ?? null;

    // Транслитерация
    $translit = '';
    for ($i = 0; $i < mb_strlen($surname); $i++) {
        $char = mb_substr($surname, $i, 1);
        $translit .= $translitMap[mb_strtolower($char)] ?? $char;
    }

    $login = $translit . 
             mb_substr($name, 0, 1) . 
             ($patronymic ? mb_substr($patronymic, 0, 1) : '');

    return $login;
}