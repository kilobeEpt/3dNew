-- Migration: Create services table
-- Description: Stores available services/products
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `services` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `short_description` VARCHAR(500) NULL,
    `image` VARCHAR(255) NULL,
    `price_type` ENUM('fixed', 'variable', 'quote') NOT NULL DEFAULT 'quote',
    `base_price` DECIMAL(10, 2) NULL,
    `unit` VARCHAR(50) NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `is_visible` BOOLEAN NOT NULL DEFAULT TRUE,
    `is_featured` BOOLEAN NOT NULL DEFAULT FALSE,
    `meta_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_services_slug` (`slug`),
    KEY `idx_services_category_id` (`category_id`),
    KEY `idx_services_is_visible` (`is_visible`),
    KEY `idx_services_is_featured` (`is_featured`),
    KEY `idx_services_display_order` (`display_order`),
    KEY `idx_services_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_services_category` FOREIGN KEY (`category_id`) 
        REFERENCES `service_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
