-- Migration: Create site_settings table
-- Description: Stores application-wide configuration settings
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NULL,
    `setting_type` ENUM('string', 'number', 'boolean', 'json', 'text') NOT NULL DEFAULT 'string',
    `group_name` VARCHAR(100) NOT NULL DEFAULT 'general',
    `description` VARCHAR(500) NULL,
    `is_public` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Can be accessed by frontend',
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_site_settings_key` (`setting_key`),
    KEY `idx_site_settings_group` (`group_name`),
    KEY `idx_site_settings_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
