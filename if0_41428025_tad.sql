-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Хост: sql311.infinityfree.com
-- Время создания: Июн 20 2026 г., 09:48
-- Версия сервера: 11.4.12-MariaDB
-- Версия PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `if0_41428025_tad`
--

-- --------------------------------------------------------

--
-- Структура таблицы `ideas`
--

CREATE TABLE `ideas` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(140) NOT NULL,
  `description` text NOT NULL,
  `sector` varchar(120) NOT NULL,
  `stage` enum('idea','mvp','business') NOT NULL DEFAULT 'idea',
  `location` varchar(120) DEFAULT NULL,
  `investment_needed` tinyint(1) NOT NULL DEFAULT 0,
  `budget_from` decimal(12,2) DEFAULT NULL,
  `budget_to` decimal(12,2) DEFAULT NULL,
  `target_audience` varchar(255) DEFAULT NULL,
  `market_note` text DEFAULT NULL,
  `monetization` varchar(255) DEFAULT NULL,
  `collaboration_open` tinyint(1) NOT NULL DEFAULT 0,
  `seeking_roles` varchar(255) DEFAULT NULL,
  `status` enum('published','archived') NOT NULL DEFAULT 'published',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `ideas`
--

INSERT INTO `ideas` (`id`, `user_id`, `title`, `description`, `sector`, `stage`, `location`, `investment_needed`, `budget_from`, `budget_to`, `target_audience`, `market_note`, `monetization`, `collaboration_open`, `seeking_roles`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Talabalar uchun AI mentor bot', 'Universitet talabalariga o\'qish rejasini tuzib beradigan, grant va amaliyot topishda yo\'l ko\'rsatadigan o\'zbekcha AI mentor bot. Telegram va webview orqali ishlaydi.', 'Ta\'lim', 'mvp', 'Onlayn', 1, '5000.00', '12000.00', 'Talabalar va yosh mutaxassislar', 'Bozor: OTM talabalari va kurs bitiruvchilari. Asosiy raqobat: umumiy chatbotlar. Farqi: o\'zbek tilida lokal universitet jarayonlari.', 'B2C obuna + universitetlar bilan B2B', 1, 'CTO, mentor, investor', 'published', '2026-03-09 23:29:23', '2026-03-16 23:29:23'),
(2, 5, 'Fermer uchun hosil tavsiya platformasi', 'Suv, tuproq va hudud bo\'yicha qaysi ekinni ekish samaraliroq ekanini tavsiya qiladigan platforma. Past internetda ishlashi uchun oflayn kesh ham bo\'ladi.', 'Qishloq xo\'jaligi', 'idea', 'Andijon', 1, '7000.00', '15000.00', 'Kichik va o\'rta fermer xo\'jaliklari', 'Agritech bozorida ma\'lumot ko\'p, ammo o\'zbekcha lokal tavsiyalar kam. Ob-havo va narx trendi bilan kuchayadi.', 'Obuna + agro kompaniyalar sponsorlik paketi', 1, 'Agronom mentor, product manager', 'published', '2026-03-11 23:29:23', '2026-03-17 23:29:23'),
(3, 2, 'Mahalliy hunarmandlar uchun live-commerce bozor', 'Hunarmandlar Instagram va Telegramga bog\'liq bo\'lmay, o\'z katalogi, to\'lov va mini live stream orqali mahsulot sotadigan marketplace.', 'Elektron savdo', 'business', 'Buxoro', 0, NULL, NULL, 'Hunarmandlar, kichik brendlar, turistlar', 'Mahalliy hunarmandlarning savdosi parokanda. Yagona katalog + live show modeli sotuvni oshiradi.', 'Komissiya + premium storefront', 0, '', 'published', '2026-03-13 23:29:23', '2026-03-17 23:29:23'),
(4, 5, 'Mikrologistika uchun talabalar kuryer tarmog\'i', 'Shahar ichidagi kichik yetkazmalarni talabalar orqali optimallashtiradigan tizim. Kuryerlar uchun bo\'sh vaqtda daromad, bizneslar uchun arzonroq xizmat.', 'Logistika', 'idea', 'Toshkent', 1, '6000.00', '18000.00', 'Kichik bizneslar va talabalar', 'Talab tez o\'smoqda, ammo tezkor va arzon segmentda bo\'shliq bor. Ishonch va marshrutlash asosiy masala.', 'Har buyurtmadan komissiya + biznes abonement', 1, 'Operations lead, mentor, investor', 'published', '2026-03-15 23:29:23', '2026-03-18 23:29:23');

-- --------------------------------------------------------

--
-- Структура таблицы `idea_comments`
--

CREATE TABLE `idea_comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `idea_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `idea_comments`
--

INSERT INTO `idea_comments` (`id`, `idea_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 4, 'Agar grant tracker va amaliyot board qo\'shsangiz, retention kuchayadi.', '2026-03-16 23:29:23'),
(2, 1, 3, 'B2B universitet paketi alohida yo\'nalish bo\'lsa, monetizatsiya yanada aniqroq bo\'ladi.', '2026-03-17 23:29:23'),
(3, 2, 4, 'Oflayn rejim juda kuchli jihat. Lekin ma\'lumot qayerdan yangilanadi, shuni aniq yozish kerak.', '2026-03-17 23:29:23'),
(4, 3, 5, 'Hunarmandlar uchun onboarding juda muhim. Video instruktsiya bilan boshlang.', '2026-03-18 03:29:23'),
(5, 4, 2, 'Talabalar tarmog\'ida ishonch uchun ID verifikatsiya va reyting tizimi kerak.', '2026-03-18 18:29:23');

-- --------------------------------------------------------

--
-- Структура таблицы `idea_votes`
--

CREATE TABLE `idea_votes` (
  `id` int(10) UNSIGNED NOT NULL,
  `idea_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `vote` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `idea_votes`
--

INSERT INTO `idea_votes` (`id`, `idea_id`, `user_id`, `vote`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(2, 1, 4, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(3, 1, 5, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(4, 2, 2, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(5, 2, 3, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(6, 2, 4, -1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(7, 3, 3, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(8, 3, 4, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(9, 3, 5, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(10, 4, 2, 1, '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(11, 4, 3, -1, '2026-03-18 23:29:23', '2026-03-18 23:29:23');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('founder','investor','mentor','admin') NOT NULL DEFAULT 'founder',
  `bio` text DEFAULT NULL,
  `location` varchar(120) DEFAULT NULL,
  `expertise` varchar(180) DEFAULT NULL,
  `contact_link` varchar(255) DEFAULT NULL,
  `collaboration_status` enum('available','selective','busy') NOT NULL DEFAULT 'available',
  `created_at` datetime NOT NULL,
  `last_seen` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `bio`, `location`, `expertise`, `contact_link`, `collaboration_status`, `created_at`, `last_seen`) VALUES
(1, 'Admin', 'admin@tadbirkor.uz', '$2y$10$huyHzT8.uWjWJLgqjbeweeua2oZeQHmqwLf2IK1aXnN8UedxiuPaW', 'admin', 'Platforma boshqaruvi uchun yaratilgan administrator akkaunti.', '', 'Boshqaruv', '', 'available', '2026-03-18 23:29:23', '2026-06-20 06:47:35'),
(2, 'Madina Karimova', 'madina@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'founder', 'Edtech va jamiyatga foydali mahsulotlar ustida ishlayman.', 'Samarqand', 'Ta\'lim, mahsulot', '@madinafounder', 'available', '2026-02-28 23:29:23', '2026-03-18 23:29:23'),
(3, 'Akmal Yusupov', 'akmal@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'investor', 'Erta bosqich loyihalarga qiziqaman.', 'Toshkent', 'Angel investitsiya, fintech', 'https://linkedin.com/in/akmalyusupov', 'selective', '2026-03-04 23:29:23', '2026-03-18 23:29:23'),
(4, 'Dilafruz Aminova', 'dilafruz@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor', 'Marketing va o\'sish bo\'yicha mentor.', 'Buxoro', 'Brending, growth', '@dilafruzmentor', 'available', '2026-03-06 23:29:23', '2026-03-18 23:29:23'),
(5, 'Javohir Islomov', 'javohir@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'founder', 'Agri va logistika kesishmasida ishlayman.', 'Andijon', 'Agri, supply chain', 'javohir@tadbirkor.uz', 'available', '2026-03-08 23:29:23', '2026-03-18 23:29:23');

-- --------------------------------------------------------

--
-- Структура таблицы `validation_votes`
--

CREATE TABLE `validation_votes` (
  `id` int(10) UNSIGNED NOT NULL,
  `idea_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `signal` enum('use','pay','mentor','invest') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `validation_votes`
--

INSERT INTO `validation_votes` (`id`, `idea_id`, `user_id`, `signal`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'invest', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(2, 1, 4, 'mentor', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(3, 1, 5, 'use', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(4, 2, 2, 'use', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(5, 2, 3, 'invest', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(6, 3, 3, 'pay', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(7, 3, 4, 'mentor', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(8, 3, 5, 'use', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(9, 4, 2, 'use', '2026-03-18 23:29:23', '2026-03-18 23:29:23'),
(10, 4, 4, 'mentor', '2026-03-18 23:29:23', '2026-03-18 23:29:23');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `ideas`
--
ALTER TABLE `ideas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ideas_user_id_index` (`user_id`),
  ADD KEY `ideas_status_created_at_index` (`status`,`created_at`);

--
-- Индексы таблицы `idea_comments`
--
ALTER TABLE `idea_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idea_comments_idea_id_index` (`idea_id`),
  ADD KEY `idea_comments_user_id_index` (`user_id`);

--
-- Индексы таблицы `idea_votes`
--
ALTER TABLE `idea_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idea_votes_unique` (`idea_id`,`user_id`),
  ADD KEY `idea_votes_user_id_index` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Индексы таблицы `validation_votes`
--
ALTER TABLE `validation_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `validation_votes_unique` (`idea_id`,`user_id`),
  ADD KEY `validation_votes_user_id_index` (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `ideas`
--
ALTER TABLE `ideas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `idea_comments`
--
ALTER TABLE `idea_comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `idea_votes`
--
ALTER TABLE `idea_votes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `validation_votes`
--
ALTER TABLE `validation_votes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `ideas`
--
ALTER TABLE `ideas`
  ADD CONSTRAINT `ideas_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `idea_comments`
--
ALTER TABLE `idea_comments`
  ADD CONSTRAINT `idea_comments_idea_fk` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `idea_comments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `idea_votes`
--
ALTER TABLE `idea_votes`
  ADD CONSTRAINT `idea_votes_idea_fk` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `idea_votes_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `validation_votes`
--
ALTER TABLE `validation_votes`
  ADD CONSTRAINT `validation_votes_idea_fk` FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `validation_votes_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
