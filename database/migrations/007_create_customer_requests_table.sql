-- Migration: Create customer_requests table
-- Description: Stores customer quote requests and inquiries
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `customer_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `request_number` VARCHAR(50) NOT NULL,
    `customer_name` VARCHAR(255) NOT NULL,
    `customer_email` VARCHAR(255) NOT NULL,
    `customer_phone` VARCHAR(50) NULL,
    `customer_company` VARCHAR(255) NULL,
    `service_id` BIGINT UNSIGNED NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `request_type` ENUM('quote', 'inquiry', 'support', 'other') NOT NULL DEFAULT 'inquiry',
    `priority` ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    `status` ENUM('new', 'pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'new',
    `assigned_to` BIGINT UNSIGNED NULL,
    `estimated_budget` DECIMAL(10, 2) NULL,
    `notes` TEXT NULL,
    `metadata` JSON NULL COMMENT 'Additional form data',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_customer_requests_number` (`request_number`),
    KEY `idx_customer_requests_email` (`customer_email`),
    KEY `idx_customer_requests_service_id` (`service_id`),
    KEY `idx_customer_requests_status` (`status`),
    KEY `idx_customer_requests_priority` (`priority`),
    KEY `idx_customer_requests_assigned_to` (`assigned_to`),
    KEY `idx_customer_requests_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_customer_requests_service` FOREIGN KEY (`service_id`) 
        REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_customer_requests_assigned` FOREIGN KEY (`assigned_to`) 
        REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
