-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySql-8.4
-- Время создания: Авг 12 2025 г., 10:33
-- Версия сервера: 8.4.4
-- Версия PHP: 8.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `cabinet`
--

-- --------------------------------------------------------

--
-- Структура таблицы `company_details`
--

CREATE TABLE `company_details` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Наименование организации',
  `full_name` varchar(255) NOT NULL COMMENT 'Полное наименование организации',
  `inn` varchar(12) NOT NULL COMMENT 'ИНН',
  `kpp` varchar(9) NOT NULL COMMENT 'КПП',
  `ogrn` varchar(15) NOT NULL COMMENT 'ОГРН',
  `regist_address` varchar(255) NOT NULL COMMENT 'Место государственной регистрации',
  `e_mail` varchar(50) NOT NULL COMMENT 'Электронный адрес',
  `phone` varchar(60) NOT NULL COMMENT 'Телефон',
  `bank_bic` varchar(9) NOT NULL COMMENT 'БИК',
  `bank_name` varchar(255) NOT NULL COMMENT 'Именование банка',
  `bank_ks` varchar(20) NOT NULL COMMENT 'к/с',
  `bank_rs` varchar(20) NOT NULL COMMENT 'р/с'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Реквизиты ООО "АРМ"';

--
-- Дамп данных таблицы `company_details`
--

INSERT INTO `company_details` (`id`, `name`, `full_name`, `inn`, `kpp`, `ogrn`, `regist_address`, `e_mail`, `phone`, `bank_bic`, `bank_name`, `bank_ks`, `bank_rs`) VALUES
(1, 'ООО «АРМ»', 'Общество с ограниченной ответственностью «АРМ»', '0276974018', '027601001', '1220200030302', '450053, Башкортостан Респ, Уфа г, Октября пр-кт, д. 132/3, ОФИС 12', 'arm2023arm@yandex.ru', '', '048073601', 'ПАО Сбербанк', '30101810300000000601', '40702810506000002136');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `company_details`
--
ALTER TABLE `company_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `company_details`
--
ALTER TABLE `company_details`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
