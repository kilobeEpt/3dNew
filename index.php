<?php

declare(strict_types=1);

/**
 * Main Entry Point Router for nginx Compatibility
 * 
 * This router handles all incoming requests and routes them appropriately:
 * - /api/* requests -> /api/index.php
 * - /admin/* requests -> /admin/index.php
 * - Static files (CSS, JS, images, HTML) -> served directly
 * - Non-existent files -> 404.html
 * 
 * RESTRUCTURED: Now operates from project root instead of public_html
 */

// Get the requested URI and remove query string
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Normalize URI (remove trailing slashes except for root)
$requestUri = $requestUri === '/' ? '/' : rtrim($requestUri, '/');

// Define base paths - now __DIR__ IS the project root (web root)
$projectRoot = __DIR__;

/**
 * Special routes: Redirect SEO files to API
 */
if ($requestUri === '/sitemap.xml') {
    $_SERVER['REQUEST_URI'] = '/api/sitemap.xml';
    chdir($projectRoot);
    require $projectRoot . '/api/index.php';
    exit;
}

if ($requestUri === '/robots.txt') {
    $_SERVER['REQUEST_URI'] = '/api/robots.txt';
    chdir($projectRoot);
    require $projectRoot . '/api/index.php';
    exit;
}

/**
 * Route /api/* requests to API handler
 */
if (strpos($requestUri, '/api') === 0) {
    chdir($projectRoot);
    require $projectRoot . '/api/index.php';
    exit;
}

/**
 * Route /admin/* requests to Admin handler
 */
if (strpos($requestUri, '/admin') === 0) {
    chdir($projectRoot);
    require $projectRoot . '/admin/index.php';
    exit;
}

/**
 * Handle static files and HTML pages
 */

// Map / to /index.html
if ($requestUri === '/') {
    $requestUri = '/index.html';
}

// Build file path
$filePath = $projectRoot . $requestUri;

// Security check: prevent directory traversal attacks
$realPath = realpath($filePath);
$realProjectRoot = realpath($projectRoot);

if ($realPath === false || strpos($realPath, $realProjectRoot) !== 0) {
    // File doesn't exist or is outside project root
    serveNotFound();
    exit;
}

// If file exists, serve it
if (is_file($realPath)) {
    // Security: Never serve PHP files as static content (except index.php through proper routing)
    $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    if ($extension === 'php') {
        serveNotFound();
        exit;
    }
    
    serveStaticFile($realPath);
    exit;
}

// If it's a directory, try to serve index.html from it
if (is_dir($realPath)) {
    $indexPath = $realPath . '/index.html';
    if (file_exists($indexPath)) {
        serveStaticFile($indexPath);
        exit;
    }
}

// File not found - serve 404 page
serveNotFound();
exit;

/**
 * Serve a static file with appropriate Content-Type header
 */
function serveStaticFile(string $filePath): void
{
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    // Map file extensions to MIME types
    $mimeTypes = [
        // HTML
        'html' => 'text/html; charset=UTF-8',
        'htm' => 'text/html; charset=UTF-8',
        
        // Stylesheets
        'css' => 'text/css; charset=UTF-8',
        
        // Scripts
        'js' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        
        // Images
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',
        
        // Fonts
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'eot' => 'application/vnd.ms-fontobject',
        
        // Documents
        'pdf' => 'application/pdf',
        'xml' => 'application/xml',
        'txt' => 'text/plain; charset=UTF-8',
        
        // Archives
        'zip' => 'application/zip',
        
        // 3D Models
        'stl' => 'application/vnd.ms-pki.stl',
        'obj' => 'text/plain',
        '3mf' => 'application/vnd.ms-package.3dmanufacturing-3dmodel+xml',
        'step' => 'application/step',
        'stp' => 'application/step',
    ];
    
    // Get MIME type from extension or use a default
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    // Set appropriate headers
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    
    // Set cache headers for static assets
    if (in_array($extension, ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'woff', 'woff2', 'ttf', 'otf', 'ico'])) {
        // Cache for 1 year
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    } else {
        // No cache for HTML and other files
        header('Cache-Control: no-cache, must-revalidate');
    }
    
    // Output file contents
    readfile($filePath);
}

/**
 * Serve 404 Not Found page
 */
function serveNotFound(): void
{
    global $projectRoot;
    
    http_response_code(404);
    
    $notFoundPage = $projectRoot . '/404.html';
    if (file_exists($notFoundPage)) {
        header('Content-Type: text/html; charset=UTF-8');
        readfile($notFoundPage);
    } else {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested page was not found.</p></body></html>';
    }
}
