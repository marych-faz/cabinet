-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySql-8.4
-- Время создания: Авг 20 2025 г., 10:28
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
-- Структура таблицы `acts`
--

CREATE TABLE `acts` (
  `id` bigint UNSIGNED NOT NULL,
  `partner_id` bigint UNSIGNED NOT NULL COMMENT 'ID партнера кому принадлежит акт',
  `num` varchar(20) NOT NULL COMMENT 'Номер акта',
  `date` date NOT NULL COMMENT 'Дата акта',
  `period_start` int DEFAULT NULL COMMENT 'Начало периода в котором находится акт',
  `period_end` date DEFAULT NULL COMMENT 'Конец периода за который составлен акт',
  `status` int NOT NULL COMMENT '0-3, 0 черновик, 1 подан администратору, 2 одобрен, 3 отклонен',
  `comment` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Комментарий'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица актов принадлежащий партнерам';

-- --------------------------------------------------------

--
-- Структура таблицы `act_detail`
--

CREATE TABLE `act_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `act_id` bigint UNSIGNED NOT NULL COMMENT 'ID акта (ID в acts)',
  `org_id` bigint UNSIGNED NOT NULL COMMENT 'ID организации (id в orgs)',
  `num` int NOT NULL COMMENT 'Порядковый номер в акте',
  `registry_number` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Реестровый номер',
  `date` date NOT NULL COMMENT 'Дата акта',
  `placement_amount` decimal(15,2) NOT NULL COMMENT 'Сумма размещения',
  `operator_payment` decimal(15,2) NOT NULL COMMENT 'Платеж оператора',
  `commission_percentage` int NOT NULL COMMENT 'Комиссия, %',
  `commission_amount` decimal(15,2) NOT NULL COMMENT 'Сумма комиссии'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Детализация актов, какие организации входят в акт и суммы';

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

-- --------------------------------------------------------

--
-- Структура таблицы `act_status_log`
--

CREATE TABLE `act_status_log` (
  `id` bigint UNSIGNED NOT NULL,
  `act_id` bigint UNSIGNED NOT NULL COMMENT 'ID Акта',
  `status` int NOT NULL COMMENT 'Какой данный статус',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата время изменения',
  `user_id` bigint NOT NULL COMMENT 'ID полюзователя который изменил'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица изменения статуса акта';

-- --------------------------------------------------------

--
-- Структура таблицы `banks`
--

CREATE TABLE `banks` (
  `name` varchar(512) NOT NULL COMMENT 'Полное наименование',
  `post` char(6) NOT NULL COMMENT 'Почтовый индекс',
  `city` varchar(256) NOT NULL COMMENT 'Город',
  `address` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Адрес',
  `bic` char(9) NOT NULL COMMENT 'БИК',
  `ks` char(20) NOT NULL COMMENT 'к/с',
  `tel` varchar(128) NOT NULL COMMENT 'Телефоны',
  `urls` text NOT NULL COMMENT 'Сайты',
  `date0` date DEFAULT NULL COMMENT 'Создан',
  `regnum` varchar(64) NOT NULL COMMENT 'Рег.номер',
  `ogrn` char(13) NOT NULL DEFAULT '' COMMENT 'ОГРН',
  `status` int NOT NULL DEFAULT '0' COMMENT 'Статус 0-Ок, 1-отозвана, 2-аннулирована, 3-в процессе регистрации',
  `upd` date DEFAULT NULL COMMENT 'актуальность'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Справочник банков РФ';

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

-- --------------------------------------------------------

--
-- Структура таблицы `documents`
--

CREATE TABLE `documents` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL COMMENT 'id кто загрузил документ',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Имя документа',
  `content` longblob NOT NULL COMMENT 'Сам документ',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Тип документа',
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата время загрузки'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица хранение документов';

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица переписки';

-- --------------------------------------------------------

--
-- Структура таблицы `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `related_entity_type` varchar(50) DEFAULT NULL COMMENT 'Тип связанной сущности (act, org, user)',
  `related_entity_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID связанной сущности'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Уведомления для пользователей';

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

-- --------------------------------------------------------

--
-- Структура таблицы `org_status_log`
--

CREATE TABLE `org_status_log` (
  `id` bigint UNSIGNED NOT NULL,
  `org_id` bigint UNSIGNED NOT NULL COMMENT 'ID Организации',
  `status` int NOT NULL COMMENT 'Какой данный статус',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата время изменения',
  `user_id` bigint NOT NULL COMMENT 'ID пользователя который изменил'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица изменения статуса организации';

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `is_admin` tinyint(1) NOT NULL COMMENT 'яваляется ли администратором',
  `login` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `is_hashed` tinyint(1) NOT NULL COMMENT 'Зашифрован ли пароль',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'ФИО пользователя',
  `dog_num` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Номер договора',
  `dog_beg_date` date NOT NULL COMMENT 'Дата договора начало',
  `dog_end_date` date NOT NULL COMMENT 'Дата договора конец',
  `phone` varchar(20) NOT NULL,
  `bank_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Имя банка',
  `bank_bik` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'БИК',
  `bank_ks` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'к/с',
  `bank_rs` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'р/с',
  `bank_verified` tinyint(1) NOT NULL COMMENT 'Банк есть в списках',
  `commission_percentage` int UNSIGNED NOT NULL COMMENT 'Комиссия, %',
  `status` int NOT NULL COMMENT '0-подана заявка, 1-разрешенние админом, 2- заблокирован админом.',
  `is_archived` tinyint(1) NOT NULL COMMENT 'в архиве',
  `archived_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица пользователей (партнеров и администраторов)';

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
-- Индексы таблицы `acts`
--
ALTER TABLE `acts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_acts_partner_id` (`partner_id`);

--
-- Индексы таблицы `act_detail`
--
ALTER TABLE `act_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_act_detail_act_id` (`act_id`);

--
-- Индексы таблицы `act_payments`
--
ALTER TABLE `act_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_act_payment_act_id` (`act_id`);

--
-- Индексы таблицы `act_status_log`
--
ALTER TABLE `act_status_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_act_status_log_act_id` (`act_id`);

--
-- Индексы таблицы `banks`
--
ALTER TABLE `banks`
  ADD UNIQUE KEY `pk_banks_bic` (`bic`),
  ADD KEY `idx_banks_name` (`name`);

--
-- Индексы таблицы `company_details`
--
ALTER TABLE `company_details`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`);

--
-- Индексы таблицы `orgs`
--
ALTER TABLE `orgs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `last_updated_by` (`last_updated_by`),
  ADD KEY `idx_orgs_partner_id` (`partner_id`) USING BTREE;

--
-- Индексы таблицы `org_status_log`
--
ALTER TABLE `org_status_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_org_status_log_org_id` (`org_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_users_login_pass` (`login`,`pass`) USING BTREE;

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
-- AUTO_INCREMENT для таблицы `acts`
--
ALTER TABLE `acts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `act_detail`
--
ALTER TABLE `act_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `act_payments`
--
ALTER TABLE `act_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `act_status_log`
--
ALTER TABLE `act_status_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `company_details`
--
ALTER TABLE `company_details`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `orgs`
--
ALTER TABLE `orgs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `org_status_log`
--
ALTER TABLE `org_status_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_contracts`
--
ALTER TABLE `user_contracts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
