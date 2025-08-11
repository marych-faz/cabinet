-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySql-8.4
-- Время создания: Авг 09 2025 г., 20:08
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
-- Структура таблицы `orgs`
--

CREATE TABLE `orgs` (
  `id` bigint UNSIGNED NOT NULL,
  `partner_id` bigint UNSIGNED NOT NULL COMMENT 'id партнера которому прикреплена организация',
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Наименование организации',
  `inn` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'ИНН',
  `kpp` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'КПП',
  `status` int NOT NULL COMMENT '0-Черновик\r\n1-На одобрение\r\n2-Одобрено\r\n3-В архиве',
  `percent` int NOT NULL COMMENT 'процент вознаграждение',
  `is_archived` int NOT NULL DEFAULT '0' COMMENT 'в архиве',
  `last_updated_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `orgs`
--

INSERT INTO `orgs` (`id`, `partner_id`, `name`, `inn`, `kpp`, `status`, `percent`, `is_archived`, `last_updated_by`) VALUES
(1, 2, 'МАДОУ ДС № 16 \"СНЕЖИНКА\"', '8609015417', '102860146', 1, 32, 0, NULL),
(2, 5, 'ООО \"КАШКАДАН\"', '8645014448', '024589357', 3, 19, 0, NULL),
(3, 3, 'ООО \"ТЕХНОПРОГРЕСС\"', '7705123456', '770501001', 1, 41, 0, NULL),
(4, 7, 'АО \"СТРОЙИНВЕСТ\"', '7734567890', '773401002', 2, 15, 0, NULL),
(5, 2, 'МБОУ \"ЛИЦЕЙ №15\"', '5256012345', '525601001', 1, 37, 0, NULL),
(6, 10, 'ООО \"АГРОТЕХ\"', '3666067890', '366601234', 3, 22, 0, NULL),
(7, 4, 'ПАО \"ЭНЕРГОБАНК\"', '4455123456', '445501001', 1, 47, 0, NULL),
(8, 6, 'ИП СМИРНОВ А.В.', '401712345678', NULL, 2, 11, 0, NULL),
(9, 8, 'ООО \"МЕДТЕХНИКА\"', '3444123456', '344401001', 1, 28, 0, NULL),
(10, 9, 'МАУ ДО \"ДВОРЕЦ ТВОРЧЕСТВА\"', '2801123456', '280101001', 1, 33, 0, NULL),
(11, 5, 'АО \"ТРАНСНЕФТЬ-СИБИРЬ\"', '7201234567', '720301001', 3, 49, 0, NULL),
(12, 2, 'ООО \"ФАРМСТАНДАРТ\"', '7728123456', '772801001', 1, 13, 0, NULL),
(13, 10, 'МБДОУ ДЕТСКИЙ САД №45', '2308123456', '230801001', 1, 24, 0, NULL),
(14, 3, 'ООО \"СТРОЙГАЗМОНТАЖ\"', '7731123456', '773101001', 2, 36, 0, NULL),
(15, 7, 'ИП КОЗЛОВА Е.С.', '410512345678', NULL, 1, 17, 0, NULL),
(16, 4, 'ООО \"ТЕЛЕКОМСЕРВИС\"', '7840123456', '784001001', 3, 44, 0, NULL),
(17, 6, 'ГБОУ ШКОЛА №1250', '7719123456', '771901001', 1, 39, 0, NULL),
(18, 9, 'ООО \"ПРОДТОРГ\"', '5027123456', '502701001', 2, 29, 0, NULL),
(19, 2, 'АО \"АВТОВАЗ\"', '6320001234', '632001001', 1, 46, 0, NULL),
(20, 8, 'МУЗ \"ГОРОДСКАЯ БОЛЬНИЦА №1\"', '5256123456', '525601001', 1, 18, 0, NULL),
(21, 5, 'ООО \"СИБНЕФТЬ\"', '5401234567', '540101001', 3, 31, 0, NULL),
(22, 10, 'ИП ПЕТРОВ И.И.', '410212345678', NULL, 1, 14, 0, NULL);

--
-- Триггеры `orgs`
--
DELIMITER $$
CREATE TRIGGER `tr_ba_org_status` BEFORE UPDATE ON `orgs` FOR EACH ROW BEGIN
  IF old.status <> new.status THEN
    INSERT INTO org_status_log (org_id, status, user_id)
    VALUES (new.id, new.status, @current_user_id);
    
    -- Создаем уведомление для партнера
    INSERT INTO notifications (user_id, message, related_entity_type, related_entity_id)
    VALUES (
      new.partner_id, 
      CONCAT('Статус организации "', new.name, '" изменен на ', 
        CASE new.status 
          WHEN 0 THEN 'новый' 
          WHEN 1 THEN 'подтвержден' 
          WHEN 2 THEN 'отклонен' 
          WHEN 3 THEN 'заблокирован' 
        END),
      'org', 
      new.id
    );
  END IF;
END
$$
DELIMITER ;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `orgs`
--
ALTER TABLE `orgs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `last_updated_by` (`last_updated_by`),
  ADD KEY `idx_orgs_partner_id` (`partner_id`) USING BTREE;

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orgs`
--
ALTER TABLE `orgs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
