-- Migration: Create audit_logs table
-- Description: Stores audit trail for important system actions
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `user_type` ENUM('admin', 'customer', 'system') NOT NULL DEFAULT 'system',
    `event_type` VARCHAR(100) NOT NULL COMMENT 'e.g., create, update, delete, login, logout',
    `auditable_type` VARCHAR(100) NULL COMMENT 'Table/model name',
    `auditable_id` BIGINT UNSIGNED NULL COMMENT 'Record ID',
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `metadata` JSON NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_logs_user_id` (`user_id`),
    KEY `idx_audit_logs_event_type` (`event_type`),
    KEY `idx_audit_logs_auditable` (`auditable_type`, `auditable_id`),
    KEY `idx_audit_logs_created_at` (`created_at`),
    CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) 
        REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
