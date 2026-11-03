USE `tadbirkor`;

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `bio`, `location`, `expertise`, `contact_link`, `collaboration_status`, `created_at`, `last_seen`) VALUES
(1, 'Admin User', 'admin@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Platformani boshqaradi va tanlovlarni kuzatadi.', 'Toshkent', 'Moderatsiya, o''sish', 'admin@tadbirkor.uz', 'busy', NOW(), NOW()),
(2, 'Madina Karimova', 'madina@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'founder', 'Edtech va jamiyatga foydali mahsulotlar ustida ishlayman.', 'Samarqand', 'Ta''lim, mahsulot', '@madinafounder', 'available', DATE_SUB(NOW(), INTERVAL 18 DAY), NOW()),
(3, 'Akmal Yusupov', 'akmal@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'investor', 'Erta bosqich loyihalarga qiziqaman.', 'Toshkent', 'Angel investitsiya, fintech', 'https://linkedin.com/in/akmalyusupov', 'selective', DATE_SUB(NOW(), INTERVAL 14 DAY), NOW()),
(4, 'Dilafruz Aminova', 'dilafruz@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor', 'Marketing va o''sish bo''yicha mentor.', 'Buxoro', 'Brending, growth', '@dilafruzmentor', 'available', DATE_SUB(NOW(), INTERVAL 12 DAY), NOW()),
(5, 'Javohir Islomov', 'javohir@tadbirkor.uz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'founder', 'Agri va logistika kesishmasida ishlayman.', 'Andijon', 'Agri, supply chain', 'javohir@tadbirkor.uz', 'available', DATE_SUB(NOW(), INTERVAL 10 DAY), NOW())
ON DUPLICATE KEY UPDATE
`name` = VALUES(`name`),
`password_hash` = VALUES(`password_hash`),
`role` = VALUES(`role`),
`bio` = VALUES(`bio`),
`location` = VALUES(`location`),
`expertise` = VALUES(`expertise`),
`contact_link` = VALUES(`contact_link`),
`collaboration_status` = VALUES(`collaboration_status`),
`last_seen` = VALUES(`last_seen`);

INSERT INTO `ideas` (`id`, `user_id`, `title`, `description`, `sector`, `stage`, `location`, `investment_needed`, `budget_from`, `budget_to`, `target_audience`, `market_note`, `monetization`, `collaboration_open`, `seeking_roles`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Talabalar uchun AI mentor bot', 'Universitet talabalariga o''qish rejasini tuzib beradigan, grant va amaliyot topishda yo''l ko''rsatadigan o''zbekcha AI mentor bot. Telegram va webview orqali ishlaydi.', 'Ta''lim', 'mvp', 'Onlayn', 1, 5000, 12000, 'Talabalar va yosh mutaxassislar', 'Bozor: OTM talabalari va kurs bitiruvchilari. Asosiy raqobat: umumiy chatbotlar. Farqi: o''zbek tilida lokal universitet jarayonlari.', 'B2C obuna + universitetlar bilan B2B', 1, 'CTO, mentor, investor', 'published', DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 5, 'Fermer uchun hosil tavsiya platformasi', 'Suv, tuproq va hudud bo''yicha qaysi ekinni ekish samaraliroq ekanini tavsiya qiladigan platforma. Past internetda ishlashi uchun oflayn kesh ham bo''ladi.', 'Qishloq xo''jaligi', 'idea', 'Andijon', 1, 7000, 15000, 'Kichik va o''rta fermer xo''jaliklari', 'Agritech bozorida ma''lumot ko''p, ammo o''zbekcha lokal tavsiyalar kam. Ob-havo va narx trendi bilan kuchayadi.', 'Obuna + agro kompaniyalar sponsorlik paketi', 1, 'Agronom mentor, product manager', 'published', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 2, 'Mahalliy hunarmandlar uchun live-commerce bozor', 'Hunarmandlar Instagram va Telegramga bog''liq bo''lmay, o''z katalogi, to''lov va mini live stream orqali mahsulot sotadigan marketplace.', 'Elektron savdo', 'business', 'Buxoro', 0, NULL, NULL, 'Hunarmandlar, kichik brendlar, turistlar', 'Mahalliy hunarmandlarning savdosi parokanda. Yagona katalog + live show modeli sotuvni oshiradi.', 'Komissiya + premium storefront', 0, '', 'published', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 5, 'Mikrologistika uchun talabalar kuryer tarmog''i', 'Shahar ichidagi kichik yetkazmalarni talabalar orqali optimallashtiradigan tizim. Kuryerlar uchun bo''sh vaqtda daromad, bizneslar uchun arzonroq xizmat.', 'Logistika', 'idea', 'Toshkent', 1, 6000, 18000, 'Kichik bizneslar va talabalar', 'Talab tez o''smoqda, ammo tezkor va arzon segmentda bo''shliq bor. Ishonch va marshrutlash asosiy masala.', 'Har buyurtmadan komissiya + biznes abonement', 1, 'Operations lead, mentor, investor', 'published', DATE_SUB(NOW(), INTERVAL 3 DAY), NOW())
ON DUPLICATE KEY UPDATE
`title` = VALUES(`title`),
`description` = VALUES(`description`),
`sector` = VALUES(`sector`),
`stage` = VALUES(`stage`),
`location` = VALUES(`location`),
`investment_needed` = VALUES(`investment_needed`),
`budget_from` = VALUES(`budget_from`),
`budget_to` = VALUES(`budget_to`),
`target_audience` = VALUES(`target_audience`),
`market_note` = VALUES(`market_note`),
`monetization` = VALUES(`monetization`),
`collaboration_open` = VALUES(`collaboration_open`),
`seeking_roles` = VALUES(`seeking_roles`),
`status` = VALUES(`status`),
`updated_at` = VALUES(`updated_at`);

INSERT INTO `idea_votes` (`idea_id`, `user_id`, `vote`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NOW(), NOW()),
(1, 4, 1, NOW(), NOW()),
(1, 5, 1, NOW(), NOW()),
(2, 2, 1, NOW(), NOW()),
(2, 3, 1, NOW(), NOW()),
(2, 4, -1, NOW(), NOW()),
(3, 3, 1, NOW(), NOW()),
(3, 4, 1, NOW(), NOW()),
(3, 5, 1, NOW(), NOW()),
(4, 2, 1, NOW(), NOW()),
(4, 3, -1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `vote` = VALUES(`vote`), `updated_at` = VALUES(`updated_at`);

DELETE FROM `idea_comments` WHERE `idea_id` IN (1, 2, 3, 4);

INSERT INTO `idea_comments` (`idea_id`, `user_id`, `content`, `created_at`) VALUES
(1, 4, 'Agar grant tracker va amaliyot board qo''shsangiz, retention kuchayadi.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 3, 'B2B universitet paketi alohida yo''nalish bo''lsa, monetizatsiya yanada aniqroq bo''ladi.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 4, 'Oflayn rejim juda kuchli jihat. Lekin ma''lumot qayerdan yangilanadi, shuni aniq yozish kerak.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 5, 'Hunarmandlar uchun onboarding juda muhim. Video instruktsiya bilan boshlang.', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(4, 2, 'Talabalar tarmog''ida ishonch uchun ID verifikatsiya va reyting tizimi kerak.', DATE_SUB(NOW(), INTERVAL 5 HOUR));

INSERT INTO `validation_votes` (`idea_id`, `user_id`, `signal`, `created_at`, `updated_at`) VALUES
(1, 3, 'invest', NOW(), NOW()),
(1, 4, 'mentor', NOW(), NOW()),
(1, 5, 'use', NOW(), NOW()),
(2, 2, 'use', NOW(), NOW()),
(2, 3, 'invest', NOW(), NOW()),
(3, 3, 'pay', NOW(), NOW()),
(3, 4, 'mentor', NOW(), NOW()),
(3, 5, 'use', NOW(), NOW()),
(4, 2, 'use', NOW(), NOW()),
(4, 4, 'mentor', NOW(), NOW())
ON DUPLICATE KEY UPDATE `signal` = VALUES(`signal`), `updated_at` = VALUES(`updated_at`);
