<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Database configuration from .env
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'database' => $_ENV['DB_NAME'] ?? '',
    'username' => $_ENV['DB_USER'] ?? '',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];

// Validate configuration
if (empty($config['database']) || empty($config['username'])) {
    echo "Error: Database configuration incomplete. Please check your .env file.\n";
    exit(1);
}

try {
    // Connect to database
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Connected to database: {$config['database']}\n\n";
    
    // Create migrations tracking table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL DEFAULT 1,
            `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_migrations_migration` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Get list of already executed migrations
    $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY id");
    $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get migration files
    $migrationFiles = glob(__DIR__ . '/migrations/*.sql');
    sort($migrationFiles);
    
    if (empty($migrationFiles)) {
        echo "No migration files found.\n";
        exit(0);
    }
    
    // Get current batch number
    $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as next_batch FROM migrations");
    $batch = $stmt->fetch()['next_batch'];
    
    $executedCount = 0;
    $skippedCount = 0;
    
    // Execute migrations
    foreach ($migrationFiles as $file) {
        $migrationName = basename($file);
        
        if (in_array($migrationName, $executedMigrations)) {
            echo "⊘ Skipped: {$migrationName} (already executed)\n";
            $skippedCount++;
            continue;
        }
        
        echo "→ Executing: {$migrationName}... ";
        
        try {
            $sql = file_get_contents($file);
            
            // Remove comments and split by semicolons
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($statement) {
                    return !empty($statement) && !preg_match('/^--/', $statement);
                }
            );
            
            // Execute each statement
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $pdo->exec($statement);
                }
            }
            
            // Record migration
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$migrationName, $batch]);
            
            echo "✓ Success\n";
            $executedCount++;
            
        } catch (PDOException $e) {
            echo "✗ Failed\n";
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════\n";
    echo "Migration Summary:\n";
    echo "  • Executed: {$executedCount}\n";
    echo "  • Skipped:  {$skippedCount}\n";
    echo "  • Total:    " . count($migrationFiles) . "\n";
    echo "═══════════════════════════════════════════\n";
    
    if ($executedCount > 0) {
        echo "\n✓ All migrations completed successfully!\n";
    } else {
        echo "\n⊘ No new migrations to execute.\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
