#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * SEO Verification Script
 * Checks that all SEO components are properly implemented
 */

echo "=== SEO Implementation Verification ===\n\n";

$errors = [];
$warnings = [];
$passed = 0;

// Check 1: SeoService exists
echo "1. Checking SeoService...\n";
if (file_exists(__DIR__ . '/src/Services/SeoService.php')) {
    echo "   ✓ SeoService.php exists\n";
    $passed++;
} else {
    $errors[] = "SeoService.php not found";
    echo "   ✗ SeoService.php not found\n";
}

// Check 2: Sitemap and Robots controllers
echo "\n2. Checking Sitemap and Robots controllers...\n";
if (file_exists(__DIR__ . '/src/Controllers/Api/SitemapController.php')) {
    echo "   ✓ SitemapController.php exists\n";
    $passed++;
} else {
    $errors[] = "SitemapController.php not found";
    echo "   ✗ SitemapController.php not found\n";
}

if (file_exists(__DIR__ . '/src/Controllers/Api/RobotsController.php')) {
    echo "   ✓ RobotsController.php exists\n";
    $passed++;
} else {
    $errors[] = "RobotsController.php not found";
    echo "   ✗ RobotsController.php not found\n";
}

// Check 3: Routes configured
echo "\n3. Checking API routes...\n";
$routesContent = file_get_contents(__DIR__ . '/api/routes.php');
if (strpos($routesContent, 'SitemapController') !== false) {
    echo "   ✓ Sitemap route configured\n";
    $passed++;
} else {
    $errors[] = "Sitemap route not configured";
    echo "   ✗ Sitemap route not configured\n";
}

if (strpos($routesContent, 'RobotsController') !== false) {
    echo "   ✓ Robots route configured\n";
    $passed++;
} else {
    $errors[] = "Robots route not configured";
    echo "   ✗ Robots route not configured\n";
}

// Check 4: HTML files have SEO meta tags
echo "\n4. Checking HTML files for SEO meta tags...\n";
$htmlFiles = glob(__DIR__ . '/public_html/*.html');
$filesChecked = 0;
$filesWithSeo = 0;

foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    $filename = basename($file);
    
    $hasTitle = strpos($content, '<title>') !== false;
    $hasDescription = strpos($content, 'name="description"') !== false;
    $hasOg = strpos($content, 'property="og:') !== false;
    $hasCanonical = strpos($content, 'rel="canonical"') !== false;
    $hasSchema = strpos($content, 'application/ld+json') !== false;
    
    $filesChecked++;
    
    if ($hasTitle && $hasDescription && $hasOg && $hasCanonical) {
        echo "   ✓ $filename has proper SEO tags\n";
        $filesWithSeo++;
    } else {
        $missing = [];
        if (!$hasTitle) $missing[] = 'title';
        if (!$hasDescription) $missing[] = 'description';
        if (!$hasOg) $missing[] = 'Open Graph';
        if (!$hasCanonical) $missing[] = 'canonical';
        
        echo "   ✗ $filename missing: " . implode(', ', $missing) . "\n";
        $warnings[] = "$filename missing SEO tags";
    }
}

if ($filesWithSeo === $filesChecked) {
    echo "   ✓ All HTML files have SEO meta tags\n";
    $passed++;
}

// Check 5: JavaScript defer attribute
echo "\n5. Checking JavaScript defer attributes...\n";
$scriptsWithoutDefer = 0;
foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    $filename = basename($file);
    
    // Check for module scripts without defer
    preg_match_all('/<script type="module"[^>]*src="[^"]*"[^>]*>/', $content, $matches);
    foreach ($matches[0] as $script) {
        if (strpos($script, 'defer') === false) {
            echo "   ⚠ $filename has script without defer: $script\n";
            $scriptsWithoutDefer++;
        }
    }
}

if ($scriptsWithoutDefer === 0) {
    echo "   ✓ All scripts use defer attribute\n";
    $passed++;
} else {
    $warnings[] = "$scriptsWithoutDefer script tags missing defer attribute";
}

// Check 6: .htaccess exists
echo "\n6. Checking .htaccess configuration...\n";
if (file_exists(__DIR__ . '/public_html/.htaccess')) {
    $htaccess = file_get_contents(__DIR__ . '/public_html/.htaccess');
    
    if (strpos($htaccess, 'mod_deflate') !== false) {
        echo "   ✓ Gzip compression configured\n";
        $passed++;
    } else {
        $warnings[] = "Gzip compression not configured in .htaccess";
        echo "   ⚠ Gzip compression not configured\n";
    }
    
    if (strpos($htaccess, 'mod_expires') !== false) {
        echo "   ✓ Browser caching configured\n";
        $passed++;
    } else {
        $warnings[] = "Browser caching not configured in .htaccess";
        echo "   ⚠ Browser caching not configured\n";
    }
    
    if (strpos($htaccess, 'webp') !== false) {
        echo "   ✓ WebP image support configured\n";
        $passed++;
    } else {
        $warnings[] = "WebP support not configured in .htaccess";
        echo "   ⚠ WebP support not configured\n";
    }
} else {
    $errors[] = ".htaccess file not found";
    echo "   ✗ .htaccess not found\n";
}

// Check 7: ImageOptimizer helper
echo "\n7. Checking ImageOptimizer helper...\n";
if (file_exists(__DIR__ . '/src/Helpers/ImageOptimizer.php')) {
    echo "   ✓ ImageOptimizer.php exists\n";
    $passed++;
} else {
    $warnings[] = "ImageOptimizer.php not found";
    echo "   ⚠ ImageOptimizer.php not found\n";
}

// Check 8: SEO documentation
echo "\n8. Checking documentation...\n";
if (file_exists(__DIR__ . '/SEO_GUIDE.md')) {
    echo "   ✓ SEO_GUIDE.md exists\n";
    $passed++;
} else {
    $warnings[] = "SEO_GUIDE.md not found";
    echo "   ⚠ SEO_GUIDE.md not found\n";
}

// Check 9: SEO settings seed
echo "\n9. Checking SEO settings seed...\n";
if (file_exists(__DIR__ . '/database/seeds/SeoSettingsSeed.php')) {
    echo "   ✓ SeoSettingsSeed.php exists\n";
    $passed++;
} else {
    $warnings[] = "SeoSettingsSeed.php not found";
    echo "   ⚠ SeoSettingsSeed.php not found\n";
}

// Summary
echo "\n=== Summary ===\n";
echo "Passed: $passed checks\n";
echo "Warnings: " . count($warnings) . "\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($warnings)) {
    echo "\nWarnings:\n";
    foreach ($warnings as $warning) {
        echo "  - $warning\n";
    }
}

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n=== Next Steps ===\n";
echo "1. Run: php database/seeds/SeoSettingsSeed.php\n";
echo "2. Configure SITE_URL in .env file\n";
echo "3. Update site settings in admin panel\n";
echo "4. Test sitemap: curl http://localhost:8000/api/sitemap.xml\n";
echo "5. Test robots.txt: curl http://localhost:8000/api/robots.txt\n";
echo "6. Validate structured data: https://search.google.com/test/rich-results\n";
echo "7. Test page speed: https://pagespeed.web.dev/\n";
echo "8. Review SEO_GUIDE.md for complete checklist\n";

if (count($errors) > 0) {
    exit(1);
}

exit(0);
