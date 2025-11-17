<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Config;
use App\Core\Database;
use App\Core\Container;
use App\Services\Mailer;
use App\Core\Logger;
use App\Core\ErrorHandler;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Initialize error handler
$errorHandler = new ErrorHandler();
$errorHandler->register();

// Initialize service container
$container = Container::getInstance();

// Register configuration service
$config = new Config();
$container->set('config', $config);

// Register logger service
$logger = new Logger(
    $_ENV['LOG_FILE'] ?? 'logs/app.log',
    $_ENV['LOG_LEVEL'] ?? 'info'
);
$container->set('logger', $logger);

// Register database connection
try {
    $database = new Database([
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_NAME'] ?? '',
        'username' => $_ENV['DB_USER'] ?? '',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ]);
    $container->set('database', $database);
} catch (Exception $e) {
    $logger->error('Database connection failed: ' . $e->getMessage());
}

// Register mailer service
$mailer = new Mailer([
    'host' => $_ENV['MAIL_HOST'] ?? '',
    'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
    'username' => $_ENV['MAIL_USERNAME'] ?? '',
    'password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? '',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Application',
]);
$container->set('mailer', $mailer);

return $container;
