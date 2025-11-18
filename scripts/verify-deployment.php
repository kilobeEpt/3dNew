#!/usr/bin/env php
<?php

/**
 * Deployment Verification Script
 * 
 * Runs pre-deployment checks to ensure the system is ready for production.
 * 
 * Usage: php scripts/verify-deployment.php
 */

declare(strict_types=1);

// Colors
$colors = [
    'red' => "\033[0;31m",
    'green' => "\033[0;32m",
    'yellow' => "\033[1;33m",
    'blue' => "\033[0;34m",
    'cyan' => "\033[0;36m",
    'reset' => "\033[0m"
];

$passed = 0;
$failed = 0;
$warnings = 0;

function printHeader(string $text): void
{
    global $colors;
    echo "\n" . $colors['blue'] . "═══════════════════════════════════════════════════════════════" . $colors['reset'] . "\n";
    echo $colors['cyan'] . "  " . $text . $colors['reset'] . "\n";
    echo $colors['blue'] . "═══════════════════════════════════════════════════════════════" . $colors['reset'] . "\n\n";
}

function checkPass(string $message): void
{
    global $colors, $passed;
    echo $colors['green'] . "✓ " . $message . $colors['reset'] . "\n";
    $passed++;
}

function checkFail(string $message): void
{
    global $colors, $failed;
    echo $colors['red'] . "✗ " . $message . $colors['reset'] . "\n";
    $failed++;
}

function checkWarn(string $message): void
{
    global $colors, $warnings;
    echo $colors['yellow'] . "⚠ " . $message . $colors['reset'] . "\n";
    $warnings++;
}

function checkInfo(string $message): void
{
    global $colors;
    echo $colors['cyan'] . "ℹ " . $message . $colors['reset'] . "\n";
}

// Start verification
printHeader("Deployment Verification");

// Check 1: PHP Version
printHeader("1. PHP Environment");

$phpVersion = phpversion();
$phpMajor = PHP_MAJOR_VERSION;
$phpMinor = PHP_MINOR_VERSION;

if ($phpMajor > 7 || ($phpMajor == 7 && $phpMinor >= 4)) {
    checkPass("PHP version: {$phpVersion} (>= 7.4 required)");
} else {
    checkFail("PHP version: {$phpVersion} (>= 7.4 required)");
}

// Check required extensions
$requiredExtensions = ['pdo_mysql', 'mbstring', 'openssl', 'json', 'fileinfo'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        checkPass("PHP extension: {$ext}");
    } else {
        checkFail("PHP extension missing: {$ext}");
    }
}

// Check optional extensions
$optionalExtensions = ['gd', 'imagick', 'zip'];
foreach ($optionalExtensions as $ext) {
    if (extension_loaded($ext)) {
        checkPass("PHP extension: {$ext} (optional)");
    } else {
        checkWarn("PHP extension missing: {$ext} (optional but recommended)");
    }
}

// Check 2: File Structure
printHeader("2. File Structure");

$requiredDirs = ['src', 'api', 'admin', 'public_html', 'database', 'templates', 'logs', 'uploads'];
foreach ($requiredDirs as $dir) {
    if (is_dir(__DIR__ . "/../{$dir}")) {
        checkPass("Directory exists: {$dir}/");
    } else {
        checkFail("Directory missing: {$dir}/");
    }
}

$requiredFiles = [
    'bootstrap.php',
    'composer.json',
    '.env.example',
    'api/index.php',
    'admin/index.php',
    'public_html/index.html',
    'database/migrate.php',
];
foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . "/../{$file}")) {
        checkPass("File exists: {$file}");
    } else {
        checkFail("File missing: {$file}");
    }
}

// Check 3: Permissions
printHeader("3. File Permissions");

$writableDirs = ['logs', 'uploads', 'backups'];
foreach ($writableDirs as $dir) {
    $path = __DIR__ . "/../{$dir}";
    if (is_dir($path)) {
        if (is_writable($path)) {
            checkPass("Directory writable: {$dir}/");
        } else {
            checkFail("Directory not writable: {$dir}/ (run: chmod 755 {$dir})");
        }
    } else {
        checkWarn("Directory doesn't exist: {$dir}/ (will be created)");
        @mkdir($path, 0755, true);
    }
}

// Check 4: Configuration
printHeader("4. Configuration");

if (file_exists(__DIR__ . '/../.env')) {
    checkPass(".env file exists");
    
    // Load .env
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
    
    // Check critical variables
    $criticalVars = [
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'DB_NAME' => null,
        'DB_USER' => null,
        'DB_PASS' => null,
        'JWT_SECRET' => null,
        'ADMIN_EMAIL' => null,
    ];
    
    foreach ($criticalVars as $var => $expectedValue) {
        if (isset($_ENV[$var]) && !empty($_ENV[$var])) {
            if ($var === 'JWT_SECRET' && $_ENV[$var] === 'your-secret-key-here') {
                checkFail("{$var} is still set to default value");
            } elseif ($expectedValue !== null && $_ENV[$var] !== $expectedValue) {
                if ($var === 'APP_ENV' || $var === 'APP_DEBUG') {
                    checkWarn("{$var}={$_ENV[$var]} (recommended: {$expectedValue} for production)");
                }
            } else {
                checkPass("{$var} is configured");
            }
        } else {
            checkFail("{$var} is not set in .env");
        }
    }
} else {
    checkFail(".env file not found (copy from .env.example)");
}

// Check 5: Dependencies
printHeader("5. Dependencies");

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    checkPass("Composer dependencies installed");
} else {
    checkFail("Composer dependencies not installed (run: composer install)");
}

if (file_exists(__DIR__ . '/../node_modules')) {
    checkPass("Node.js dependencies installed");
} else {
    checkWarn("Node.js dependencies not installed (run: npm install)");
}

// Check 6: Database Connection
printHeader("6. Database Connection");

try {
    require_once __DIR__ . '/../bootstrap.php';
    $container = App\Core\Container::getInstance();
    $db = $container->get('database');
    checkPass("Database connection successful");
    
    // Check if tables exist
    $tables = $db->query("SHOW TABLES");
    if (count($tables) > 0) {
        checkPass("Database has " . count($tables) . " tables");
    } else {
        checkWarn("Database is empty (run: php database/migrate.php)");
    }
} catch (Exception $e) {
    checkFail("Database connection failed: " . $e->getMessage());
}

// Check 7: Security
printHeader("7. Security Checks");

// Check .htaccess files
$htaccessFiles = ['public_html/.htaccess', 'api/.htaccess', 'admin/.htaccess'];
foreach ($htaccessFiles as $file) {
    if (file_exists(__DIR__ . "/../{$file}")) {
        checkPass(".htaccess exists: {$file}");
        
        // Check if HTTPS redirect is enabled
        $content = file_get_contents(__DIR__ . "/../{$file}");
        if (strpos($content, 'RewriteCond %{HTTPS} off') !== false) {
            if (preg_match('/^#.*RewriteCond.*HTTPS.*off/m', $content)) {
                checkWarn("HTTPS redirect is commented out in {$file}");
            } else {
                checkPass("HTTPS redirect enabled in {$file}");
            }
        }
    } else {
        checkFail(".htaccess missing: {$file}");
    }
}

// Check 8: Scripts
printHeader("8. Maintenance Scripts");

$scripts = [
    'scripts/backup-database.sh',
    'scripts/backup-files.sh',
    'scripts/check-errors.php',
    'scripts/rotate-logs.php',
    'scripts/cleanup-temp.php',
    'scripts/generate-sitemap.php',
    'scripts/deploy.sh',
];

foreach ($scripts as $script) {
    if (file_exists(__DIR__ . "/../{$script}")) {
        if (is_executable(__DIR__ . "/../{$script}")) {
            checkPass("Script executable: {$script}");
        } else {
            checkWarn("Script not executable: {$script} (run: chmod +x {$script})");
        }
    } else {
        checkFail("Script missing: {$script}");
    }
}

// Summary
printHeader("Verification Summary");

$total = $passed + $failed + $warnings;

echo "\n";
echo $colors['green'] . "  Passed:   {$passed}" . $colors['reset'] . "\n";
echo $colors['red'] . "  Failed:   {$failed}" . $colors['reset'] . "\n";
echo $colors['yellow'] . "  Warnings: {$warnings}" . $colors['reset'] . "\n";
echo "  ──────────────────\n";
echo "  Total:    {$total}\n\n";

if ($failed > 0) {
    echo $colors['red'] . "  ✗ Deployment verification FAILED" . $colors['reset'] . "\n";
    echo "    Please fix the issues above before deploying.\n\n";
    exit(1);
} elseif ($warnings > 0) {
    echo $colors['yellow'] . "  ⚠ Deployment verification PASSED with warnings" . $colors['reset'] . "\n";
    echo "    You may proceed, but review warnings above.\n\n";
    exit(0);
} else {
    echo $colors['green'] . "  ✓ Deployment verification PASSED" . $colors['reset'] . "\n";
    echo "    System is ready for deployment!\n\n";
    exit(0);
}
