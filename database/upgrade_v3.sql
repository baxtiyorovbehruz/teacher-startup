-- ============================================================
-- Tadbirkor.uz — upgrade_v3.sql
-- Avatar, cover_image va password_resets ni qo'shish
-- ============================================================

ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `avatar` varchar(120) DEFAULT NULL AFTER `collaboration_status`;

ALTER TABLE `ideas`
    ADD COLUMN IF NOT EXISTS `cover_image` varchar(120) DEFAULT NULL AFTER `seeking_roles`;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `password_resets_email_index` (`email`),
  UNIQUE KEY `password_resets_token_unique` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
