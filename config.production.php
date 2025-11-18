<?php

/**
 * Production Configuration Template
 * 
 * This file provides a reference for production-specific configuration.
 * Copy this file to config.php and adjust values for your environment.
 * 
 * Note: Most configuration should be in .env file. This file is for
 * environment-specific overrides and hosting-specific settings.
 */

declare(strict_types=1);

return [
    
    // Shared Hosting Path Configuration
    'paths' => [
        'root' => '/home/c/ch167436/3dPrint',
        'public' => '/home/c/ch167436/3dPrint/public_html',
        'api' => '/home/c/ch167436/3dPrint/api',
        'admin' => '/home/c/ch167436/3dPrint/admin',
        'uploads' => '/home/c/ch167436/3dPrint/uploads',
        'logs' => '/home/c/ch167436/3dPrint/logs',
        'backups' => '/home/c/ch167436/3dPrint/backups',
    ],
    
    // URL Configuration
    'urls' => [
        'site' => 'https://yourdomain.com',
        'api' => 'https://yourdomain.com/api',
        'admin' => 'https://yourdomain.com/admin',
        'cdn' => '', // Optional CDN URL
    ],
    
    // PHP Configuration Overrides (if .htaccess or php.ini not available)
    'php' => [
        'max_execution_time' => 120,
        'memory_limit' => '256M',
        'upload_max_filesize' => '10M',
        'post_max_size' => '10M',
        'display_errors' => false,
        'error_reporting' => E_ALL & ~E_DEPRECATED & ~E_STRICT,
        'log_errors' => true,
    ],
    
    // Database Configuration (overrides .env if needed)
    'database' => [
        // Leave empty to use .env values
        'host' => '',
        'port' => 3306,
        'name' => '',
        'user' => '',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        
        // Connection options
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ],
    ],
    
    // Cache Configuration
    'cache' => [
        'enabled' => true,
        'driver' => 'file', // file, memcached, redis
        'ttl' => 3600, // Default cache TTL in seconds
        'prefix' => '3dprint_',
    ],
    
    // Session Configuration
    'session' => [
        'name' => '3DPRINT_SESSION',
        'lifetime' => 7200, // 2 hours
        'secure' => true, // HTTPS only
        'httponly' => true,
        'samesite' => 'Lax',
    ],
    
    // File Upload Configuration
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB in bytes
        'allowed_extensions' => ['stl', 'obj', '3mf', 'step', 'stp'],
        'allowed_mime_types' => [
            'application/sla',
            'application/octet-stream',
            'model/stl',
            'model/obj',
            'application/3mf',
        ],
    ],
    
    // Image Upload Configuration
    'image' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
        'thumbnail_size' => 300, // pixels
        'max_width' => 1920,
        'max_height' => 1080,
        'jpeg_quality' => 85,
        'webp_quality' => 80,
    ],
    
    // Email Configuration
    'email' => [
        'queue' => false, // Set to true if using email queue
        'retry_attempts' => 3,
        'retry_delay' => 60, // seconds
    ],
    
    // Security Configuration
    'security' => [
        'csrf_token_expiry' => 3600, // 1 hour
        'jwt_expiry' => 3600, // 1 hour for access token
        'jwt_refresh_expiry' => 604800, // 7 days for refresh token
        'password_min_length' => 8,
        'failed_login_attempts' => 5,
        'login_lockout_duration' => 900, // 15 minutes
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 100,
            'window' => 60, // seconds
        ],
    ],
    
    // Logging Configuration
    'logging' => [
        'enabled' => true,
        'level' => 'warning', // debug, info, warning, error, critical
        'max_files' => 30,
        'rotation' => true,
        'compress_after_days' => 7,
    ],
    
    // Backup Configuration
    'backup' => [
        'database' => [
            'enabled' => true,
            'retention_days' => 30,
            'compress' => true,
        ],
        'files' => [
            'enabled' => true,
            'retention_days' => 56, // 8 weeks
            'compress' => true,
            'directories' => ['uploads'],
        ],
        'offsite' => [
            'enabled' => false,
            'provider' => '', // ftp, s3, google_drive, dropbox
            'credentials' => [],
        ],
    ],
    
    // Monitoring Configuration
    'monitoring' => [
        'error_threshold' => 5,
        'check_interval' => 3600, // hourly
        'alert_email' => '', // Leave empty to use ADMIN_EMAIL from .env
        'uptime_check' => [
            'enabled' => false,
            'url' => '', // External monitoring service webhook
        ],
    ],
    
    // SEO Configuration
    'seo' => [
        'sitemap_frequency' => 'daily',
        'robots_allow_all' => true,
        'structured_data_enabled' => true,
    ],
    
    // Feature Flags
    'features' => [
        'calculator' => true,
        'gallery' => true,
        'news' => true,
        'contact_form' => true,
        'admin_panel' => true,
        'api_public' => true,
    ],
    
    // Third-Party Services
    'services' => [
        'google_analytics' => [
            'enabled' => false,
            'tracking_id' => '',
        ],
        'google_recaptcha' => [
            'enabled' => true,
            'version' => 2, // 2 or 3
        ],
        'hcaptcha' => [
            'enabled' => false,
        ],
    ],
    
    // Development/Debug (should all be false in production)
    'debug' => [
        'sql_logging' => false,
        'email_debugging' => false,
        'show_errors' => false,
        'profiling' => false,
    ],
    
];
