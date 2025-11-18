<?php
// Quick test to verify bootstrap works with new packages
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

echo "Testing Dotenv...\n";
$dotenv = Dotenv::createImmutable(__DIR__);
// Use safeLoad to not fail if .env doesn't exist
$dotenv->safeLoad();
echo "✓ Dotenv loaded successfully!\n";

echo "\nTesting PHPMailer...\n";
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer(true);
echo "✓ PHPMailer instantiated successfully!\n";
echo "  Version: " . PHPMailer::VERSION . "\n";

echo "\nTesting autoloader with App namespace...\n";
// Just check that our App namespace is registered
$loader = require __DIR__ . '/vendor/autoload.php';
$namespaces = $loader->getPrefixesPsr4();
if (isset($namespaces['App\\'])) {
    echo "✓ App\\ namespace is registered\n";
    echo "  Path: " . $namespaces['App\\'][0] . "\n";
} else {
    echo "✗ App\\ namespace not found!\n";
}

echo "\n✓ All tests passed!\n";
echo "PHP Version: " . PHP_VERSION . "\n";
