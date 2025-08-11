function fioToLogin($fullName) {
    $translitMap = [
        'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'g',  'д' => 'd',
        'е' => 'e',  'ё' => 'e',  'ж' => 'zh', 'з' => 'z',  'и' => 'i',
        'й' => 'y',  'к' => 'k',  'л' => 'l',  'м' => 'm',  'н' => 'n',
        'о' => 'o',  'п' => 'p',  'р' => 'r',  'с' => 's',  'т' => 't',
        'у' => 'u',  'ф' => 'f',  'х' => 'h',  'ц' => 'c',  'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'shch','ъ' => '',   'ы' => 'y',  'ь' => '',
        'э' => 'e',  'ю' => 'yu', 'я' => 'ya',
        'А' => 'A',  'Б' => 'B',  'В' => 'V',  'Г' => 'G',  'Д' => 'D',
        'Е' => 'E',  'Ё' => 'E',  'Ж' => 'Zh','З' => 'Z',  'И' => 'I',
        'Й' => 'Y',  'К' => 'K',  'Л' => 'L',  'М' => 'M',  'Н' => 'N',
        'О' => 'O',  'П' => 'P',  'Р' => 'R',  'С' => 'S',  'Т' => 'T',
        'У' => 'U',  'Ф' => 'F',  'Х' => 'H',  'Ц' => 'C',  'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Shch','Ъ' => '',  'Ы' => 'Y',  'Ь' => '',
        'Э' => 'E',  'Ю' => 'Yu', 'Я' => 'Ya'
    ];

    $parts = preg_split('/\s+/', trim($fullName));
    if (count($parts) < 3) {
        throw new Exception("Введите полное ФИО");
    }

    list($surname, $name, $patronymic) = $parts;

    $translit = function ($char) use ($translitMap) {
        return $translitMap[$char] ?? $char;
    };

    $login = '';
    $login .= $translit(mb_substr($surname, 0, 1));
    $login .= mb_strtolower(preg_replace_callback('/./u', function($m) use ($translitMap) {
        return $translitMap[$m[0]] ?? '';
    }, mb_substr($surname, 1)));

    $login .= strtoupper($translit(mb_substr($name, 0, 1)));
    $login .= strtoupper($translit(mb_substr($patronymic, 0, 1)));

    return $login;
}

// Пример:
echo fioToLogin("Фазуллин Марат Ильевич"); // FazullinMI