-- Migration: Create gallery_items table
-- Description: Stores portfolio/gallery images and media
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `gallery_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` ENUM('image', 'video', 'document') NOT NULL DEFAULT 'image',
    `file_size` INT UNSIGNED NULL COMMENT 'Size in bytes',
    `mime_type` VARCHAR(100) NULL,
    `width` INT UNSIGNED NULL,
    `height` INT UNSIGNED NULL,
    `thumbnail_path` VARCHAR(255) NULL,
    `category` VARCHAR(100) NULL,
    `tags` JSON NULL,
    `service_id` BIGINT UNSIGNED NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `is_visible` BOOLEAN NOT NULL DEFAULT TRUE,
    `is_featured` BOOLEAN NOT NULL DEFAULT FALSE,
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `uploaded_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_gallery_items_slug` (`slug`),
    KEY `idx_gallery_items_file_type` (`file_type`),
    KEY `idx_gallery_items_category` (`category`),
    KEY `idx_gallery_items_service_id` (`service_id`),
    KEY `idx_gallery_items_is_visible` (`is_visible`),
    KEY `idx_gallery_items_is_featured` (`is_featured`),
    KEY `idx_gallery_items_display_order` (`display_order`),
    KEY `idx_gallery_items_uploaded_by` (`uploaded_by`),
    KEY `idx_gallery_items_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_gallery_items_service` FOREIGN KEY (`service_id`) 
        REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_gallery_items_uploaded_by` FOREIGN KEY (`uploaded_by`) 
        REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
