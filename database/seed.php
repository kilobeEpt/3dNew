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
    
    // Get seed files
    $seedFiles = glob(__DIR__ . '/seeds/*.php');
    sort($seedFiles);
    
    if (empty($seedFiles)) {
        echo "No seed files found.\n";
        exit(0);
    }
    
    $seededCount = 0;
    $totalRecords = 0;
    
    // Execute seeds
    foreach ($seedFiles as $file) {
        $seedName = basename($file);
        echo "→ Processing: {$seedName}... ";
        
        try {
            $seedData = require $file;
            
            if (!isset($seedData['table']) || !isset($seedData['data'])) {
                echo "✗ Invalid seed format\n";
                continue;
            }
            
            $table = $seedData['table'];
            $data = $seedData['data'];
            
            if (empty($data)) {
                echo "⊘ No data\n";
                continue;
            }
            
            $insertedCount = 0;
            
            foreach ($data as $record) {
                // Build insert query
                $columns = array_keys($record);
                $placeholders = array_fill(0, count($columns), '?');
                
                $sql = sprintf(
                    "INSERT INTO `%s` (`%s`) VALUES (%s)",
                    $table,
                    implode('`, `', $columns),
                    implode(', ', $placeholders)
                );
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($record));
                $insertedCount++;
            }
            
            echo "✓ Inserted {$insertedCount} record(s)\n";
            $seededCount++;
            $totalRecords += $insertedCount;
            
        } catch (PDOException $e) {
            // Check if it's a duplicate entry error
            if ($e->getCode() === '23000') {
                echo "⊘ Skipped (records already exist)\n";
            } else {
                echo "✗ Failed\n";
                echo "Error: " . $e->getMessage() . "\n";
            }
        } catch (Exception $e) {
            echo "✗ Failed\n";
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════\n";
    echo "Seeding Summary:\n";
    echo "  • Files Processed: {$seededCount}\n";
    echo "  • Records Inserted: {$totalRecords}\n";
    echo "  • Total Files: " . count($seedFiles) . "\n";
    echo "═══════════════════════════════════════════\n";
    
    if ($totalRecords > 0) {
        echo "\n✓ Database seeded successfully!\n";
    } else {
        echo "\n⊘ No new records inserted.\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
