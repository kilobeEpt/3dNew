-- Migration: Create analytics_events table
-- Description: Stores user interaction and usage analytics
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `analytics_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_type` VARCHAR(100) NOT NULL COMMENT 'calculator_view, calculator_submit, calculator_error, etc.',
    `event_category` VARCHAR(50) NOT NULL COMMENT 'calculator, contact, gallery, etc.',
    `event_data` JSON NULL COMMENT 'Flexible event metadata',
    `user_session_id` VARCHAR(255) NULL,
    `user_ip` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `page_url` VARCHAR(500) NULL,
    `referrer` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_analytics_events_type` (`event_type`),
    KEY `idx_analytics_events_category` (`event_category`),
    KEY `idx_analytics_events_session` (`user_session_id`),
    KEY `idx_analytics_events_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
