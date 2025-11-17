-- Migration: Create cost_estimates table
-- Description: Stores detailed cost estimates for customer requests
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `cost_estimates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `estimate_number` VARCHAR(50) NOT NULL,
    `customer_request_id` BIGINT UNSIGNED NULL,
    `customer_name` VARCHAR(255) NOT NULL,
    `customer_email` VARCHAR(255) NOT NULL,
    `customer_phone` VARCHAR(50) NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `subtotal` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `tax_rate` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    `tax_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
    `status` ENUM('draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired') NOT NULL DEFAULT 'draft',
    `valid_until` DATE NULL,
    `notes` TEXT NULL,
    `terms_conditions` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `sent_at` TIMESTAMP NULL,
    `viewed_at` TIMESTAMP NULL,
    `accepted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cost_estimates_number` (`estimate_number`),
    KEY `idx_cost_estimates_customer_request_id` (`customer_request_id`),
    KEY `idx_cost_estimates_customer_email` (`customer_email`),
    KEY `idx_cost_estimates_status` (`status`),
    KEY `idx_cost_estimates_created_by` (`created_by`),
    KEY `idx_cost_estimates_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_cost_estimates_request` FOREIGN KEY (`customer_request_id`) 
        REFERENCES `customer_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_cost_estimates_created_by` FOREIGN KEY (`created_by`) 
        REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
