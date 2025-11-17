-- Migration: Create cost_estimate_items table
-- Description: Stores line items for cost estimates
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `cost_estimate_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `estimate_id` BIGINT UNSIGNED NOT NULL,
    `item_type` ENUM('service', 'material', 'custom') NOT NULL,
    `item_id` BIGINT UNSIGNED NULL COMMENT 'Service ID or Material ID',
    `description` VARCHAR(500) NOT NULL,
    `quantity` DECIMAL(10, 2) NOT NULL DEFAULT 1.00,
    `unit` VARCHAR(50) NOT NULL DEFAULT 'unit',
    `unit_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `line_total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cost_estimate_items_estimate_id` (`estimate_id`),
    KEY `idx_cost_estimate_items_item_type_id` (`item_type`, `item_id`),
    KEY `idx_cost_estimate_items_display_order` (`display_order`),
    CONSTRAINT `fk_cost_estimate_items_estimate` FOREIGN KEY (`estimate_id`) 
        REFERENCES `cost_estimates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
