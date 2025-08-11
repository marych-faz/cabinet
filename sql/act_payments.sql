-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySql-8.4
-- Время создания: Авг 10 2025 г., 13:48
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
-- Структура таблицы `act_payments`
--

CREATE TABLE `act_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `act_id` bigint UNSIGNED NOT NULL COMMENT 'ID акта',
  `num` int NOT NULL COMMENT 'Номер платежки',
  `date` date NOT NULL COMMENT 'Дата оплаты',
  `summa` decimal(15,2) NOT NULL COMMENT 'Сумма оплаты',
  `comment` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Комментарий к платежу'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Платежи по акту';

--
-- Дамп данных таблицы `act_payments`
--

INSERT INTO `act_payments` (`id`, `act_id`, `num`, `date`, `summa`, `comment`) VALUES
(1, 1, 152, '2025-08-09', 50000.00, 'Оплата первого акта'),
(2, 2, 532, '2025-08-05', 1000.00, 'Частичная оплата'),
(3, 2, 853, '2025-08-07', 1200.00, 'Частичная вторая оплата');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `act_payments`
--
ALTER TABLE `act_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_act_payment_act_id` (`act_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `act_payments`
--
ALTER TABLE `act_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
