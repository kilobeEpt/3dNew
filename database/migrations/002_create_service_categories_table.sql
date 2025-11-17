-- Migration: Create service_categories table
-- Description: Stores hierarchical service categories
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `service_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(255) NULL,
    `image` VARCHAR(255) NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `is_visible` BOOLEAN NOT NULL DEFAULT TRUE,
    `meta_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_service_categories_slug` (`slug`),
    KEY `idx_service_categories_parent_id` (`parent_id`),
    KEY `idx_service_categories_is_visible` (`is_visible`),
    KEY `idx_service_categories_display_order` (`display_order`),
    KEY `idx_service_categories_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_service_categories_parent` FOREIGN KEY (`parent_id`) 
        REFERENCES `service_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
