-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySql-8.4
-- Время создания: Авг 19 2025 г., 15:25
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
-- Структура таблицы `user_contracts`
--

CREATE TABLE `user_contracts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL COMMENT 'id агента',
  `number` varchar(20) NOT NULL COMMENT 'Номер договора',
  `start_date` date NOT NULL COMMENT 'Начало договора',
  `end_date` date NOT NULL COMMENT 'Конец договора',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Статус договора 1-черновик, 2-активный, 3-просроченный, 4-прекращенный',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания договора',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата  договора',
  `is_archived` tinyint(1) DEFAULT '0',
  `comment` varchar(255) DEFAULT NULL COMMENT 'Коментарий к договору'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица договоров с агентами';

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `user_contracts`
--
ALTER TABLE `user_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `user_contracts`
--
ALTER TABLE `user_contracts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `user_contracts`
--
ALTER TABLE `user_contracts`
  ADD CONSTRAINT `user_contracts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
