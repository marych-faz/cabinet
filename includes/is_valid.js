<script>
    // Функция для проверки правильности ИНН
    function is_valid_inn(i)
    {
        if ( i.match(/\D/) ) return false;

        var inn = i.match(/(\d)/g);

        if ( inn.length == 10 )
        {
            return inn[9] == String(((
                        2*inn[0] + 4*inn[1] + 10*inn[2] +
                        3*inn[3] + 5*inn[4] +  9*inn[5] +
                        4*inn[6] + 6*inn[7] +  8*inn[8]
                    ) % 11) % 10);
        }
        else if ( inn.length == 12 )
        {
            return inn[10] == String(((
                        7*inn[0] + 2*inn[1] + 4*inn[2] +
                        10*inn[3] + 3*inn[4] + 5*inn[5] +
                        9*inn[6] + 4*inn[7] + 6*inn[8] +
                        8*inn[9]
                    ) % 11) % 10) && inn[11] == String(((
                        3*inn[0] +  7*inn[1] + 2*inn[2] +
                        4*inn[3] + 10*inn[4] + 3*inn[5] +
                        5*inn[6] +  9*inn[7] + 4*inn[8] +
                        6*inn[9] +  8*inn[10]
                    ) % 11) % 10);
        }

        return false;
    }

function is_valid_ogrn(ogrn) {
    //дальше работаем со строкой
    ogrn += '';

    //для ОГРН в 13 знаков
    if(ogrn.length == 13 && (ogrn.slice(12,13) == ((ogrn.slice(0,-1))%11 + '').slice(-1))){
        return true;

    //для ОГРН в 15 знаков
    }else if(ogrn.length == 15 && (ogrn.slice(14,15) == ((ogrn.slice(0,-1))%13 + '').slice(-1))){
        return true;

    }else{
        return false;
    }
}

function validateKs(ks, bik, error) {
    var result = false;
    if (validateBik(bik, error)) {
        if (typeof ks === 'number') {
            ks = ks.toString();
        } else if (typeof ks !== 'string') {
            ks = '';
        }
        if (!ks.length) {
            error.code = 1;
            error.message = 'К/С пуст';
        } else if (/[^0-9]/.test(ks)) {
            error.code = 2;
            error.message = 'К/С может состоять только из цифр';
        } else if (ks.length !== 20) {
            error.code = 3;
            error.message = 'К/С может состоять только из 20 цифр';
        } else {
            var bikKs = '0' + bik.toString().slice(4, 6) + ks;
            var checksum = 0;
            var coefficients = [7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1];
            for (var i in coefficients) {
                checksum += coefficients[i] * (bikKs[i] % 10);
            }
            if (checksum % 10 === 0) {
                result = true;
            } else {
                error.code = 4;
                error.message = 'Неправильное контрольное число';
            }
        }
    }
    return result;
}

function validateRs(rs, bik, error) {
    var result = false;
    if (validateBik(bik, error)) {
        if (typeof rs === 'number') {
            rs = rs.toString();
        } else if (typeof rs !== 'string') {
            rs = '';
        }
        if (!rs.length) {
            error.code = 1;
            error.message = 'Р/С пуст';
        } else if (/[^0-9]/.test(rs)) {
            error.code = 2;
            error.message = 'Р/С может состоять только из цифр';
        } else if (rs.length !== 20) {
            error.code = 3;
            error.message = 'Р/С может состоять только из 20 цифр';
        } else {
            var bikRs = bik.toString().slice(-3) + rs;
            var checksum = 0;
            var coefficients = [7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1];
            for (var i in coefficients) {
                checksum += coefficients[i] * (bikRs[i] % 10);
            }
            if (checksum % 10 === 0) {
                result = true;
            } else {
                error.code = 4;
                error.message = 'Неправильное контрольное число';
            }
        }
    }
    return result;
}
function validateBik(bik, error) {
    var result = false;
    if (typeof bik === 'number') {
        bik = bik.toString();
    } else if (typeof bik !== 'string') {
        bik = '';
    }
    if (!bik.length) {
        error.code = 1;
        error.message = 'БИК пуст';
    } else if (/[^0-9]/.test(bik)) {
        error.code = 2;
        error.message = 'БИК может состоять только из цифр';
    } else if (bik.length !== 9) {
        error.code = 3;
        error.message = 'БИК может состоять только из 9 цифр';
    } else {
        result = true;
    }
    return result;
}
</script>