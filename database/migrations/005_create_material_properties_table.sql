-- Migration: Create material_properties table
-- Description: Stores custom properties/attributes for materials
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `material_properties` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `material_id` BIGINT UNSIGNED NOT NULL,
    `property_name` VARCHAR(100) NOT NULL,
    `property_value` TEXT NOT NULL,
    `property_type` ENUM('text', 'number', 'boolean', 'date', 'json') NOT NULL DEFAULT 'text',
    `display_order` INT NOT NULL DEFAULT 0,
    `is_searchable` BOOLEAN NOT NULL DEFAULT FALSE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_material_properties_material_id` (`material_id`),
    KEY `idx_material_properties_property_name` (`property_name`),
    KEY `idx_material_properties_is_searchable` (`is_searchable`),
    CONSTRAINT `fk_material_properties_material` FOREIGN KEY (`material_id`) 
        REFERENCES `materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
