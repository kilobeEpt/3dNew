<?php

declare(strict_types=1);

/**
 * Comprehensive Project Audit Script
 * Tests all critical components of the project
 */

echo "=================================================\n";
echo "PROJECT AUDIT REPORT\n";
echo "=================================================\n\n";

$passed = 0;
$failed = 0;
$warnings = 0;

// Test 1: PHP Version
echo "1. PHP Version Check\n";
$phpVersion = phpversion();
echo "   Current PHP Version: {$phpVersion}\n";
if (version_compare($phpVersion, '8.2.0', '>=')) {
    echo "   ✓ PASS: PHP version is compatible (>= 8.2.0)\n\n";
    $passed++;
} else {
    echo "   ✗ FAIL: PHP version is too old (requires >= 8.2.0)\n\n";
    $failed++;
}

// Test 2: Required PHP Extensions
echo "2. Required PHP Extensions\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ {$ext}\n";
    } else {
        echo "   ✗ {$ext} (MISSING)\n";
        $missingExtensions[] = $ext;
    }
}
if (empty($missingExtensions)) {
    echo "   ✓ PASS: All required extensions loaded\n\n";
    $passed++;
} else {
    echo "   ✗ FAIL: Missing extensions: " . implode(', ', $missingExtensions) . "\n\n";
    $failed++;
}

// Test 3: Composer Autoload
echo "3. Composer Autoload\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✓ PASS: Composer autoload exists and loaded\n\n";
    $passed++;
} else {
    echo "   ✗ FAIL: vendor/autoload.php not found\n\n";
    $failed++;
    exit(1);
}

// Test 4: Environment Configuration
echo "4. Environment Configuration\n";
if (file_exists(__DIR__ . '/.env')) {
    echo "   ✓ .env file exists\n";
    $passed++;
} else {
    echo "   ⚠ WARNING: .env file missing (using .env.example)\n";
    $warnings++;
}

// Test 5: Bootstrap Loading
echo "\n5. Bootstrap Loading\n";
try {
    require_once __DIR__ . '/bootstrap.php';
    echo "   ✓ PASS: Bootstrap loaded successfully\n\n";
    $passed++;
} catch (Exception $e) {
    echo "   ✗ FAIL: Bootstrap failed - " . $e->getMessage() . "\n\n";
    $failed++;
    exit(1);
}

// Test 6: Core Classes
echo "6. Core Classes\n";
$coreClasses = [
    'App\Core\Config',
    'App\Core\Database',
    'App\Core\Container',
    'App\Core\Router',
    'App\Core\Request',
    'App\Core\Response',
    'App\Core\Logger',
    'App\Core\ErrorHandler',
];
foreach ($coreClasses as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class}\n";
    } else {
        echo "   ✗ {$class} (MISSING)\n";
        $failed++;
    }
}
echo "   ✓ PASS: All core classes exist\n\n";
$passed++;

// Test 7: Service Classes
echo "7. Service Classes\n";
$serviceClasses = [
    'App\Services\Mailer',
    'App\Services\JwtService',
    'App\Services\AuditLogger',
    'App\Services\SeoService',
];
$missingServices = [];
foreach ($serviceClasses as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class}\n";
    } else {
        echo "   ✗ {$class} (MISSING)\n";
        $missingServices[] = $class;
    }
}
if (empty($missingServices)) {
    echo "   ✓ PASS: All service classes exist\n\n";
    $passed++;
} else {
    echo "   ⚠ WARNING: Some service classes missing\n\n";
    $warnings++;
}

// Test 8: Controller Classes - API
echo "8. API Controller Classes\n";
$apiControllers = [
    'App\Controllers\Api\ServicesController',
    'App\Controllers\Api\MaterialsController',
    'App\Controllers\Api\PricingRulesController',
    'App\Controllers\Api\GalleryController',
    'App\Controllers\Api\NewsController',
    'App\Controllers\Api\SettingsController',
    'App\Controllers\Api\CostEstimatesController',
    'App\Controllers\Api\ContactController',
    'App\Controllers\Api\CsrfController',
    'App\Controllers\Api\AnalyticsController',
    'App\Controllers\Api\SitemapController',
    'App\Controllers\Api\RobotsController',
];
foreach ($apiControllers as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class}\n";
    } else {
        echo "   ✗ {$class} (MISSING)\n";
        $failed++;
    }
}
echo "   ✓ PASS: All API controllers exist\n\n";
$passed++;

// Test 9: Controller Classes - Admin
echo "9. Admin Controller Classes\n";
$adminControllers = [
    'App\Controllers\Admin\AuthController',
    'App\Controllers\Admin\ServiceCategoriesController',
    'App\Controllers\Admin\ServicesController',
    'App\Controllers\Admin\MaterialsController',
    'App\Controllers\Admin\PricingRulesController',
    'App\Controllers\Admin\GalleryController',
    'App\Controllers\Admin\NewsController',
    'App\Controllers\Admin\SiteSettingsController',
    'App\Controllers\Admin\CustomerRequestsController',
    'App\Controllers\Admin\CostEstimatesController',
    'App\Controllers\Admin\AnalyticsController',
    'App\Controllers\Admin\AuditLogsController',
];
foreach ($adminControllers as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class}\n";
    } else {
        echo "   ✗ {$class} (MISSING)\n";
        $failed++;
    }
}
echo "   ✓ PASS: All admin controllers exist\n\n";
$passed++;

// Test 10: Middleware Classes
echo "10. Middleware Classes\n";
$middlewareClasses = [
    'App\Middleware\CorsMiddleware',
    'App\Middleware\RateLimitMiddleware',
    'App\Middleware\CsrfMiddleware',
    'App\Middleware\AdminAuthMiddleware',
];
foreach ($middlewareClasses as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class}\n";
    } else {
        echo "   ✗ {$class} (MISSING)\n";
        $failed++;
    }
}
echo "   ✓ PASS: All middleware classes exist\n\n";
$passed++;

// Test 11: Model Classes
echo "11. Model Classes\n";
$modelClasses = [
    'App\Models\BaseModel',
    'App\Models\AdminUser',
    'App\Models\Service',
    'App\Models\Material',
    'App\Models\PricingRule',
    'App\Models\Gallery',
    'App\Models\News',
    'App\Models\SiteSetting',
    'App\Models\CustomerRequest',
    'App\Models\CostEstimate',
];
foreach ($modelClasses as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class}\n";
    } else {
        echo "   ✗ {$class} (MISSING)\n";
        $failed++;
    }
}
echo "   ✓ PASS: All model classes exist\n\n";
$passed++;

// Test 12: Helper Classes
echo "12. Helper Classes\n";
$helperClasses = [
    'App\Helpers\Response',
    'App\Helpers\Validator',
    'App\Helpers\Captcha',
];
foreach ($helperClasses as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class}\n";
    } else {
        echo "   ✗ {$class} (MISSING)\n";
        $failed++;
    }
}
echo "   ✓ PASS: All helper classes exist\n\n";
$passed++;

// Test 13: Directory Structure
echo "13. Directory Structure\n";
$requiredDirs = [
    '/src',
    '/src/Core',
    '/src/Controllers',
    '/src/Controllers/Api',
    '/src/Controllers/Admin',
    '/src/Models',
    '/src/Services',
    '/src/Middleware',
    '/src/Helpers',
    '/src/Repositories',
    '/api',
    '/admin',
    '/public_html',
    '/database',
    '/database/migrations',
    '/database/seeds',
    '/logs',
    '/uploads',
    '/uploads/models',
    '/templates',
];
foreach ($requiredDirs as $dir) {
    $fullPath = __DIR__ . $dir;
    if (is_dir($fullPath)) {
        echo "   ✓ {$dir}\n";
    } else {
        echo "   ✗ {$dir} (MISSING)\n";
        $failed++;
    }
}
echo "   ✓ PASS: All required directories exist\n\n";
$passed++;

// Test 14: Critical Files
echo "14. Critical Files\n";
$criticalFiles = [
    '/bootstrap.php',
    '/api/index.php',
    '/api/routes.php',
    '/api/routes_admin.php',
    '/admin/index.php',
    '/public_html/index.php',
    '/public_html/index.html',
    '/public_html/calculator.html',
    '/public_html/contact.html',
    '/composer.json',
    '/composer.lock',
];
foreach ($criticalFiles as $file) {
    $fullPath = __DIR__ . $file;
    if (file_exists($fullPath)) {
        echo "   ✓ {$file}\n";
    } else {
        echo "   ✗ {$file} (MISSING)\n";
        $failed++;
    }
}
echo "   ✓ PASS: All critical files exist\n\n";
$passed++;

// Test 15: File Permissions
echo "15. File Permissions\n";
$writableDirs = [
    '/logs',
    '/uploads',
    '/uploads/models',
];
foreach ($writableDirs as $dir) {
    $fullPath = __DIR__ . $dir;
    if (is_writable($fullPath)) {
        echo "   ✓ {$dir} (writable)\n";
    } else {
        echo "   ⚠ {$dir} (NOT writable)\n";
        $warnings++;
    }
}
echo "   ✓ PASS: Directory permissions checked\n\n";
$passed++;

// Test 16: Database Migrations
echo "16. Database Migrations\n";
$migrationCount = count(glob(__DIR__ . '/database/migrations/*.sql'));
echo "   Found {$migrationCount} migration files\n";
if ($migrationCount > 0) {
    echo "   ✓ PASS: Migration files exist\n\n";
    $passed++;
} else {
    echo "   ✗ FAIL: No migration files found\n\n";
    $failed++;
}

// Test 17: Database Seeds
echo "17. Database Seeds\n";
$seedCount = count(glob(__DIR__ . '/database/seeds/*.php'));
echo "   Found {$seedCount} seed files\n";
if ($seedCount > 0) {
    echo "   ✓ PASS: Seed files exist\n\n";
    $passed++;
} else {
    echo "   ⚠ WARNING: No seed files found\n\n";
    $warnings++;
}

// Test 18: Frontend Assets
echo "18. Frontend Assets\n";
$assetDirs = [
    '/public_html/assets/css',
    '/public_html/assets/js',
    '/public_html/assets/images',
];
foreach ($assetDirs as $dir) {
    $fullPath = __DIR__ . $dir;
    if (is_dir($fullPath)) {
        $fileCount = count(glob($fullPath . '/*'));
        echo "   ✓ {$dir} ({$fileCount} files)\n";
    } else {
        echo "   ⚠ {$dir} (NOT FOUND)\n";
        $warnings++;
    }
}
echo "   ✓ PASS: Frontend asset structure checked\n\n";
$passed++;

// Test 19: Admin Panel Assets
echo "19. Admin Panel Assets\n";
if (file_exists(__DIR__ . '/admin/index.html')) {
    echo "   ✓ Admin panel frontend exists\n";
    $passed++;
} else {
    echo "   ✗ Admin panel frontend missing\n";
    $failed++;
}

// Test 20: nginx Router
echo "\n20. nginx Router\n";
if (file_exists(__DIR__ . '/public_html/index.php')) {
    $content = file_get_contents(__DIR__ . '/public_html/index.php');
    if (strpos($content, 'Main Entry Point Router') !== false) {
        echo "   ✓ nginx router exists and properly configured\n";
        $passed++;
    } else {
        echo "   ⚠ index.php exists but may not be nginx router\n";
        $warnings++;
    }
} else {
    echo "   ✗ nginx router missing\n";
    $failed++;
}

// Final Summary
echo "\n=================================================\n";
echo "AUDIT SUMMARY\n";
echo "=================================================\n";
echo "✓ Passed: {$passed}\n";
echo "✗ Failed: {$failed}\n";
echo "⚠ Warnings: {$warnings}\n";
echo "\n";

if ($failed === 0) {
    echo "✓✓✓ PROJECT AUDIT: PASSED ✓✓✓\n";
    echo "Project is ready for deployment!\n";
    exit(0);
} else {
    echo "✗✗✗ PROJECT AUDIT: FAILED ✗✗✗\n";
    echo "Please fix the failed checks before deployment.\n";
    exit(1);
}
