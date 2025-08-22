<?php
/**
 * Функция проверяет правильность инн
 *
 * @param string $inn
 * @return bool
 */
function is_valid_inn( $inn )
{
    if ( preg_match('/\D/', $inn) ) return false;

    $inn = (string) $inn;
    $len = strlen($inn);

    if ( $len === 10 )
    {
        return $inn[9] === (string) (((
                    2*$inn[0] + 4*$inn[1] + 10*$inn[2] +
                    3*$inn[3] + 5*$inn[4] +  9*$inn[5] +
                    4*$inn[6] + 6*$inn[7] +  8*$inn[8]
                ) % 11) % 10);
    }
    elseif ( $len === 12 )
    {
        $num10 = (string) (((
                    7*$inn[0] + 2*$inn[1] + 4*$inn[2] +
                    10*$inn[3] + 3*$inn[4] + 5*$inn[5] +
                    9*$inn[6] + 4*$inn[7] + 6*$inn[8] +
                    8*$inn[9]
                ) % 11) % 10);

        $num11 = (string) (((
                    3*$inn[0] +  7*$inn[1] + 2*$inn[2] +
                    4*$inn[3] + 10*$inn[4] + 3*$inn[5] +
                    5*$inn[6] +  9*$inn[7] + 4*$inn[8] +
                    6*$inn[9] +  8*$inn[10]
                ) % 11) % 10);

        return $inn[11] === $num11 && $inn[10] === $num10;
    }

    return false;
}

/**
* Функция проверяет правильность КПП
*
* @param string $kpp
* @return bool
*/

function is_valid_kpp($kpp) {
    return preg_match('#^\d{9}$#', $kpp);
}

/**
* Проверка ОГРН и ОГРНИП
* входные параметры:
* ogrn
* Проверяет только валидность. Для проверки действительности используйте /service/org
* @param array $args
* @return array
*/
function is_valid_ogrn($ogrn)
{
    if (!preg_match('#^\d{13,15}$#', $ogrn)){
        return false; // ОГРН должен состоять из 13 или 15 цифр

    } elseif ($ogrn > PHP_INT_MAX) {
        return false; // Проверка невозможна, т.к. скрипт запущен на 32х-разрядной версии PHP
    }
//делаем строкой
    $ogrn = $ogrn . '';
    if (strlen($ogrn) == 13 and $ogrn[12] != substr((substr($ogrn, 0, -1) % 11), -1)){
        return false; // 'Контрольное число равно ' . substr((substr($ogrn, 0, -1) % 11), -1) . '. Ожидалось ' . $ogrn[12]);
    } elseif (strlen($ogrn) == 15 and $ogrn[14] != substr(substr($ogrn, 0, -1) % 13, -1)) {
        return false; //  'Контрольное число равно ' . substr(substr($ogrn, 0, -1) % 13, -1) . '. Ожидалось ' . $ogrn[14]);
    } elseif (strlen($ogrn) != 13 and strlen($ogrn) != 15) {
        return false; // 'ОГРН должен состоять из 13 или 15 цифр');
    }
    return true;
}

function is_valid_ks($bic, $ks) {
    if (!is_valid_biс($bic)) return false; // неверный БИК
    if(empty($ks) || !preg_match('#^\d{20}$#', $ks)) return false; // к/с должен состоять из 20 цифр

    $bik_ks = '0' . substr((string) $bic, -5, 2) . $ks;
    $checksum = 0;
    foreach ([7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1] as $i => $k) {
    $checksum += $k * ((int) $bik_ks{$i} % 10);
    }
    if ($checksum % 10 === 0) {
        return true;
    } else {
        return false; // Неверный контрольный разряд
    }
}

function is_valid_biс($bic) {
    if(empty($bic))return false; // Не передан обязательный параметр bic
    if(!preg_match('#^\d{9}$#', $bik)) return false; // БИК должен состоять из 9 цифр
    return true;
}

function is_valid_rs($bic, $rs) {
    if (!is_valid_biс($bic)) return false; // неверный БИК
    if(empty($rs) || !preg_match('#^\d{20}$#', $rs)) return false; // р/с должен состоять из 20 цифр

    $bik_rs = substr((string) $bic, -3) . $rs;
    $checksum = 0;
    foreach ([7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1] as $i => $k) {
        $checksum += $k * ((int) $bik_rs{$i} % 10);
    }
    if ($checksum % 10 === 0) {
        return true;
    } else {
        return false; // Неверный контрольный разряд
    }
}

function is_valid_biс($bic) {
    if(empty($bic))return false; // Не передан обязательный параметр bic
    if(!preg_match('#^\d{9}$#', $bik)) return false; // БИК должен состоять из 9 цифр
    return true;
}

