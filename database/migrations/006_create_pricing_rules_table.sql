-- Migration: Create pricing_rules table
-- Description: Stores dynamic pricing rules for services and materials
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `pricing_rules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `rule_type` ENUM('service', 'material', 'global') NOT NULL,
    `target_id` BIGINT UNSIGNED NULL COMMENT 'Service ID or Material ID',
    `condition_type` ENUM('quantity', 'date_range', 'customer_type', 'total_amount') NOT NULL,
    `condition_value` JSON NOT NULL COMMENT 'Flexible condition parameters',
    `discount_type` ENUM('percentage', 'fixed_amount', 'fixed_price') NOT NULL,
    `discount_value` DECIMAL(10, 2) NOT NULL,
    `priority` INT NOT NULL DEFAULT 0,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `valid_from` DATE NULL,
    `valid_to` DATE NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `idx_pricing_rules_rule_type` (`rule_type`),
    KEY `idx_pricing_rules_target_id` (`target_id`),
    KEY `idx_pricing_rules_is_active` (`is_active`),
    KEY `idx_pricing_rules_priority` (`priority`),
    KEY `idx_pricing_rules_valid_dates` (`valid_from`, `valid_to`),
    KEY `idx_pricing_rules_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
