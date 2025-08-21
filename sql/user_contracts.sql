-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySql-8.4
-- Время создания: Авг 20 2025 г., 10:25
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
-- Дамп данных таблицы `user_contracts`
--

INSERT INTO `user_contracts` (`id`, `user_id`, `number`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `is_archived`, `comment`) VALUES
(31, 1, '123/456', '2025-01-01', '2025-12-31', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(32, 2, '321/456', '2025-01-01', '2025-07-31', 3, '2025-08-19 18:14:26', '2025-08-19 18:18:50', 0, 'Комментарий к договору'),
(33, 3, '789/123', '2025-03-15', '2026-03-15', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(34, 4, '456/789', '2025-02-01', '2026-02-01', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(35, 5, '987/654', '2025-05-10', '2026-05-10', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(36, 6, '654/321', '2025-04-20', '2026-04-20', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(37, 7, '222/333', '2025-01-05', '2026-01-05', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(38, 8, '555/666', '2024-12-12', '2025-12-12', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(39, 9, '777/888', '2025-03-03', '2026-03-03', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(40, 10, '999/000', '2025-06-25', '2026-06-25', 3, '2025-08-19 18:14:26', '2025-08-19 18:50:59', 0, NULL),
(41, 11, '452/6565', '2025-01-01', '2025-12-31', 2, '2025-08-19 18:14:26', '2025-08-19 18:14:26', 0, NULL),
(46, 2, '321/003', '2025-08-01', '2025-12-31', 2, '2025-08-19 18:18:30', '2025-08-19 18:18:30', 0, 'Дополнительный договор'),
(47, 10, '999/003', '2025-06-26', '2025-12-31', 2, '2025-08-19 18:50:43', '2025-08-19 18:50:43', 0, 'Перезаключен');

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
