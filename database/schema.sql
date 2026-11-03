CREATE DATABASE IF NOT EXISTS `tadbirkor`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `tadbirkor`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('founder', 'investor', 'mentor', 'admin') NOT NULL DEFAULT 'founder',
  `bio` TEXT NULL,
  `location` VARCHAR(120) NULL,
  `expertise` VARCHAR(180) NULL,
  `contact_link` VARCHAR(255) NULL,
  `collaboration_status` ENUM('available', 'selective', 'busy') NOT NULL DEFAULT 'available',
  `avatar` VARCHAR(120) NULL,
  `created_at` DATETIME NOT NULL,
  `last_seen` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ideas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(140) NOT NULL,
  `description` TEXT NOT NULL,
  `sector` VARCHAR(120) NOT NULL,
  `stage` ENUM('idea', 'mvp', 'business') NOT NULL DEFAULT 'idea',
  `location` VARCHAR(120) NULL,
  `investment_needed` TINYINT(1) NOT NULL DEFAULT 0,
  `budget_from` DECIMAL(12,2) NULL,
  `budget_to` DECIMAL(12,2) NULL,
  `target_audience` VARCHAR(255) NULL,
  `market_note` TEXT NULL,
  `monetization` VARCHAR(255) NULL,
  `collaboration_open` TINYINT(1) NOT NULL DEFAULT 0,
  `seeking_roles` VARCHAR(255) NULL,
  `cover_image` VARCHAR(120) NULL,
  `status` ENUM('published', 'archived') NOT NULL DEFAULT 'published',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ideas_user_id_index` (`user_id`),
  KEY `ideas_status_created_at_index` (`status`, `created_at`),
  CONSTRAINT `ideas_user_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `idea_votes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `idea_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `vote` TINYINT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idea_votes_unique` (`idea_id`, `user_id`),
  KEY `idea_votes_user_id_index` (`user_id`),
  CONSTRAINT `idea_votes_idea_fk`
    FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `idea_votes_user_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `idea_comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `idea_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idea_comments_idea_id_index` (`idea_id`),
  KEY `idea_comments_user_id_index` (`user_id`),
  CONSTRAINT `idea_comments_idea_fk`
    FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `idea_comments_user_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS `validation_votes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `idea_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `signal` ENUM('use', 'pay', 'mentor', 'invest') NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `validation_votes_unique` (`idea_id`, `user_id`),
  KEY `validation_votes_user_id_index` (`user_id`),
  CONSTRAINT `validation_votes_idea_fk`
    FOREIGN KEY (`idea_id`) REFERENCES `ideas` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `validation_votes_user_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
