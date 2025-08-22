-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySql-8.4
-- Время создания: Авг 22 2025 г., 18:43
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
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `is_admin` tinyint(1) NOT NULL COMMENT 'яваляется ли администратором',
  `login` varchar(50) NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Электронная почта',
  `pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `is_hashed` tinyint(1) NOT NULL COMMENT 'Зашифрован ли пароль',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'ФИО пользователя',
  `inn` varchar(12) DEFAULT NULL COMMENT 'ИНН (ФЛ=12, ЮЛ=10)',
  `kpp` varchar(9) DEFAULT NULL COMMENT 'КПП организации (ЮЛ)',
  `director_name` varchar(50) NOT NULL COMMENT 'Имя директора (ЮЛ)',
  `birthday` date DEFAULT NULL COMMENT 'День рождения (ФЛ,ИП)',
  `document_details` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Реквизиты документа (ФЛ,ИП)',
  `ogrn` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'ОГРН (ЮЛ)',
  `ogrnip` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'ОГРНИП (ИП)',
  `address` varchar(255) NOT NULL COMMENT 'Адрес регистрации',
  `dog_num` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Номер договора',
  `dog_beg_date` date NOT NULL COMMENT 'Дата договора начало',
  `dog_end_date` date NOT NULL COMMENT 'Дата договора конец',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Телефон',
  `tax_form` varchar(50) NOT NULL COMMENT 'Форма налогообложения ',
  `bank_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Имя банка',
  `bank_inn` varchar(10) NOT NULL COMMENT 'ИНН банка',
  `bank_bik` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'БИК',
  `bank_ks` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'к/с',
  `bank_rs` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'р/с',
  `bank_verified` tinyint(1) NOT NULL COMMENT 'Банк есть в списках',
  `commission_percentage` int UNSIGNED NOT NULL COMMENT 'Комиссия, %',
  `status` int NOT NULL COMMENT '0-подана заявка, 1-разрешенние админом, 2- заблокирован админом.',
  `is_archived` tinyint(1) NOT NULL COMMENT 'в архиве',
  `archived_date` date DEFAULT NULL,
  `agent_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Статус партнера \r\n0-не определен\r\n1-Физическое лицо\r\n2-Индивидуальный предприниматель\r\n3-Юридическое лицо',
  `privacy_consent` tinyint(1) DEFAULT '0' COMMENT 'Согласие на обработку персональных данных',
  `privacy_consent_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата время согласие на обработку персональных данных'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица пользователей (партнеров и администраторов)';

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `is_admin`, `login`, `email`, `pass`, `is_hashed`, `name`, `inn`, `kpp`, `director_name`, `birthday`, `document_details`, `ogrn`, `ogrnip`, `address`, `dog_num`, `dog_beg_date`, `dog_end_date`, `phone`, `tax_form`, `bank_name`, `bank_inn`, `bank_bik`, `bank_ks`, `bank_rs`, `bank_verified`, `commission_percentage`, `status`, `is_archived`, `archived_date`, `agent_status`, `privacy_consent`, `privacy_consent_date`) VALUES
(1, 1, 'Admin', 'admin@gmail.com', '$2y$12$wDd/3hFzUAa1vWk0NFhq1eAL1kZZQuRbrg/iyt41V9IpBvUTVl5Xi', 1, 'Королева Алевтина Ивановна', NULL, NULL, '', NULL, '', '', '', '', '123/456', '2025-01-01', '2025-12-31', '+7(927)785-22-55', '', 'АКБ «Абсолют Банк» (ПАО)', '', '044525976', '30101810500000000976', '40817810099910004312', 0, 0, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51'),
(2, 0, 'User', 'user@gmail.com', '$2y$12$X9tY.t3NmrZ6eVgUZxRKmOYW8tVdth5IxFgfu2/Ie35cRCLHNRLdS', 1, 'Большаков Давид Тихонович', NULL, NULL, '', NULL, '', '', '', '', '321/456', '2025-01-01', '2025-12-31', '+7(917)532-45-12', '', 'ПАО «АК БАРС» БАНК', '', '049205805', '30101810000000000805', '40702810680060657001', 0, 15, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51'),
(3, 0, 'IvanovI', 'ivanov@mail.ru', '$2y$12$t54V5gEa2Q9rGAxzewKgxeR/wdUWNVvldOZu8hsbPtHVnmBHdcrkO', 1, 'Иванов Иван Сергеевич', NULL, NULL, '', NULL, '', '', '', '', '789/123', '2025-03-15', '2026-03-15', '+7(987)654-32-10', '', 'ПАО Сбербанк', '', '044525225', '30101810400000000225', '40817810700012345678', 0, 0, 0, 1, NULL, 0, NULL, '2025-08-22 16:40:51'),
(4, 0, 'PetrovaM', 'petrova@yandex.ru', '$2y$12$Tzwk8dhTIT4Upw1Zx91wCu6UG1ZnPbUvFlSBay93EqZjXcTOQ3XTy', 1, 'Петрова Мария Владимировна', NULL, NULL, '', NULL, '', '', '', '', '456/789', '2025-02-01', '2026-02-01', '+7(917)123-45-67', '', 'ВТБ (ПАО)', '', '044525187', '30101810700000000187', '40701810000001122334', 0, 0, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51'),
(5, 0, 'SidorovA', 'sidorov@gmail.com', '$2y$12$wvGDQS4SKDA.BTIUe.FTG.7YexgV64NOaiGqtTAyJYickMfpXAGym', 1, 'Сидоров Алексей Петрович', NULL, NULL, '', NULL, '', '', '', '', '987/654', '2025-05-10', '2026-05-10', '+7(927)987-65-43', '', 'АО «Райффайзенбанк»', '', '044525700', '30101810200000000700', '40817810500009876543', 0, 0, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51'),
(6, 0, 'KuznetsovaE', 'kuznetsova@mail.ru', '$2y$12$wg4IqoJS4fYPd4ptekOvy.g4sdFB1ZpfHupoB5P5f9zG92TSr8Ji.', 1, 'Кузнецова Елена Дмитриевна', NULL, NULL, '', NULL, '', '', '', '', '654/321', '2025-04-20', '2026-04-20', '+7(917)555-44-33', '', 'ПАО «Промсвязьбанк»', '', '044525555', '30101810400000000555', '40701810600001112222', 0, 0, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51'),
(7, 0, 'FedorovD', 'fedorov@yandex.ru', '$2y$12$KqnF0Ww54vIpwXql4IZ.Ruu0xCIv8NENr4r3sqAMHaSqNrVDBVKD6', 1, 'Федоров Дмитрий Николаевич', NULL, NULL, '', NULL, '', '', '', '', '222/333', '2025-01-05', '2026-01-05', '+7(927)111-22-33', '', 'АО «Тинькофф Банк»', '', '044525974', '30101810145250000974', '40817810700003334455', 0, 0, 0, 1, NULL, 0, NULL, '2025-08-22 16:40:51'),
(8, 0, 'SmirnovaO', 'smirnova@gmail.com', '$2y$12$GHy7TMQMGKvIUwvsUhKH6e4Oue7ET9YTcPJYauskfV/3Svo5eg486', 1, 'Смирнова Ольга Васильевнай', NULL, NULL, '', NULL, '', '', '', '', '555/666', '2024-12-12', '2025-12-12', '+7(917)999-88-77', '', 'ПАО «МОСКОВСКИЙ КРЕДИТНЫЙ БАНК»', '', '044525092', '30101810300000000092', '40701810200005556677', 0, 0, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51'),
(9, 0, 'NikolaevA', 'nikolaev@mail.ru', '$2y$12$4SQzYBjAJ0ttHSXj4d5b8uNPboNtkqEGoG9Y.625dVERamKEeZYmS', 1, 'Николаев Артем Игоревич', NULL, NULL, '', NULL, '', '', '', '', '777/888', '2025-03-03', '2026-03-03', '+7(927)333-44-55', '', 'ПАО «РОСБАНК»', '', '044525256', '30101810000000000256', '40702810400007778899', 0, 0, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51'),
(10, 0, 'VolkovaA', 'volkova@yandex.ru', '$2y$12$1g.qW7ylS5AmuNH31JND1eIUUQ14VX/56UcQ02IaNNVvK8Kr4If82', 1, 'Волкова Анна Михайловна', NULL, NULL, '', NULL, '', '', '', '', '999/000', '2025-06-25', '2026-06-25', '+7(917)777-66-55', '', 'ПАО «Совкомбанк»', '', '044525341', '30101810400000000341', '40701810500009990000', 0, 0, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51'),
(11, 0, 'FazullinMI', 'marych@mail.com', '$2y$12$oRRoimjsPY8fyGgCJFbRg.BzFO1RM1h1VApXtDuLH51qUQsg.DAPG', 1, 'Фазуллин Марат Ильевич', NULL, NULL, '', NULL, '', '', '', '', '452/6565', '2025-01-01', '2025-12-31', '+79578545', '', 'Отдел N 14 УФК по Краснодарскому краю', '', '000396186', '40116810903960010002', '40012320001212016952', 0, 0, 0, 0, NULL, 0, NULL, '2025-08-22 16:40:51');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_users_login_pass` (`login`,`pass`) USING BTREE;

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
