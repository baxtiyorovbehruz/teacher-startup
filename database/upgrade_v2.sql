USE `tadbirkor`;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'contact_link'
    ),
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `contact_link` VARCHAR(255) NULL AFTER `expertise`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'collaboration_status'
    ),
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `collaboration_status` ENUM(''available'', ''selective'', ''busy'') NOT NULL DEFAULT ''available'' AFTER `contact_link`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ideas'
          AND COLUMN_NAME = 'collaboration_open'
    ),
    'SELECT 1',
    'ALTER TABLE `ideas` ADD COLUMN `collaboration_open` TINYINT(1) NOT NULL DEFAULT 0 AFTER `monetization`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    EXISTS(
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'ideas'
          AND COLUMN_NAME = 'seeking_roles'
    ),
    'SELECT 1',
    'ALTER TABLE `ideas` ADD COLUMN `seeking_roles` VARCHAR(255) NULL AFTER `collaboration_open`'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
