-- Migration: Create system_logs table
-- Description: Stores application errors and system events
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `level` ENUM('emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug') NOT NULL DEFAULT 'info',
    `message` TEXT NOT NULL,
    `context` JSON NULL,
    `channel` VARCHAR(100) NOT NULL DEFAULT 'application',
    `exception_class` VARCHAR(255) NULL,
    `exception_message` TEXT NULL,
    `exception_trace` LONGTEXT NULL,
    `file` VARCHAR(500) NULL,
    `line` INT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `url` VARCHAR(1000) NULL,
    `method` VARCHAR(10) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_system_logs_level` (`level`),
    KEY `idx_system_logs_channel` (`channel`),
    KEY `idx_system_logs_user_id` (`user_id`),
    KEY `idx_system_logs_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
