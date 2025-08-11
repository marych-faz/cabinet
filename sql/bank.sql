CREATE TABLE IF NOT EXISTS bank (
    name VARCHAR(512) NOT NULL COMMENT 'Полное наименование',
    post CHAR(6) NOT NULL COMMENT 'Почтовый индекс',
    city VARCHAR(256) NOT NULL COMMENT 'Город',
    address VARCHAR(128) NOT NULL COMMENT 'Адрес',
    bic CHAR(9) NOT NULL UNIQUE COMMENT 'БИК',
    ks  CHAR(20) NOT NULL COMMENT 'к/с',
    tel VARCHAR(128) NOT NULL COMMENT 'Телефоны',
    urls TEXT NOT NULL COMMENT 'Сайты',
    date0 DATE COMMENT 'Создан',
    regnum VARCHAR(64) NOT NULL COMMENT 'Рег.номер',
    ogrn CHAR(13) NOT NULL DEFAULT '' COMMENT 'ОГРН',
	status INT(1) NOT NULL DEFAULT '0' COMMENT 'Статус 0-Ок, 1-отозвана, 2-аннулирована, 3-в процессе регистрации',
    upd DATE COMMENT 'актуальность'
);