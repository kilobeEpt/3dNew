<?php
// SEO Template - Head section with dynamic meta tags
// This file should be included in the <head> section of all pages

use App\Services\SeoService;
use App\Core\Container;

$container = Container::getInstance();
$database = $container->get('database');
$seoService = new SeoService($database);

// Page-specific SEO config (should be set before including this template)
$seoConfig = $seoConfig ?? [];

// Generate and output meta tags
echo $seoService->generateMetaTags($seoConfig);

// Generate and output structured data
$schemas = $schemas ?? [];

// Always include organization schema on all pages
if (!isset($includeOrganizationSchema) || $includeOrganizationSchema !== false) {
    $schemas[] = $seoService->generateOrganizationSchema();
}

// Output all schemas
if (!empty($schemas)) {
    echo $seoService->renderMultipleJsonLd($schemas);
}
?>
<!-- Preconnect to required origins for performance -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php if (!empty($_ENV['CDN_URL'])): ?>
<link rel="preconnect" href="<?= htmlspecialchars($_ENV['CDN_URL']) ?>">
<?php endif; ?>
