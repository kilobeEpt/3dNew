-- Migration: Create materials table
-- Description: Stores materials/components used in services
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `materials` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `sku` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `category` VARCHAR(100) NULL,
    `unit` VARCHAR(50) NOT NULL DEFAULT 'unit',
    `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `stock_quantity` DECIMAL(10, 2) NULL,
    `min_order_quantity` DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
    `supplier` VARCHAR(255) NULL,
    `supplier_sku` VARCHAR(100) NULL,
    `image` VARCHAR(255) NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_materials_slug` (`slug`),
    UNIQUE KEY `uk_materials_sku` (`sku`),
    KEY `idx_materials_category` (`category`),
    KEY `idx_materials_is_active` (`is_active`),
    KEY `idx_materials_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
