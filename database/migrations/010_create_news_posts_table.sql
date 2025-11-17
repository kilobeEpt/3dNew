-- Migration: Create news_posts table
-- Description: Stores blog posts and news articles
-- Created: 2024-01-01

CREATE TABLE IF NOT EXISTS `news_posts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `author_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` VARCHAR(500) NULL,
    `content` LONGTEXT NOT NULL,
    `featured_image` VARCHAR(255) NULL,
    `category` VARCHAR(100) NULL,
    `tags` JSON NULL,
    `status` ENUM('draft', 'published', 'scheduled', 'archived') NOT NULL DEFAULT 'draft',
    `is_featured` BOOLEAN NOT NULL DEFAULT FALSE,
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `published_at` TIMESTAMP NULL,
    `meta_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_news_posts_slug` (`slug`),
    KEY `idx_news_posts_author_id` (`author_id`),
    KEY `idx_news_posts_status` (`status`),
    KEY `idx_news_posts_category` (`category`),
    KEY `idx_news_posts_is_featured` (`is_featured`),
    KEY `idx_news_posts_published_at` (`published_at`),
    KEY `idx_news_posts_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_news_posts_author` FOREIGN KEY (`author_id`) 
        REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
