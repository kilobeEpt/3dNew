#!/usr/bin/env php
<?php

/**
 * Sitemap Generation Script
 * 
 * Generates sitemap.xml file for search engines.
 * This is a static generation that can be run via cron.
 * 
 * Usage: php scripts/generate-sitemap.php
 * Cron: 0 2 * * * cd /home/c/ch167436/3dPrint && php scripts/generate-sitemap.php >> logs/cron.log 2>&1
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Container;

// ANSI colors
$colors = [
    'green' => "\033[0;32m",
    'blue' => "\033[0;34m",
    'yellow' => "\033[1;33m",
    'reset' => "\033[0m"
];

function logMessage(string $message, string $color = 'reset'): void
{
    global $colors;
    echo $colors[$color] . "[" . date('Y-m-d H:i:s') . "] {$message}" . $colors['reset'] . PHP_EOL;
}

try {
    logMessage("Generating sitemap...", 'blue');
    
    $container = Container::getInstance();
    $database = $container->get('database');
    
    // Get site URL from environment
    $siteUrl = $_ENV['SITE_URL'] ?? $_ENV['APP_URL'] ?? 'http://localhost';
    $siteUrl = rtrim($siteUrl, '/');
    
    // Start XML
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    
    $urlset = $xml->createElement('urlset');
    $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $xml->appendChild($urlset);
    
    // Static pages
    $staticPages = [
        ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['loc' => '/services.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['loc' => '/calculator.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['loc' => '/gallery.html', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => '/about.html', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => '/contact.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ];
    
    foreach ($staticPages as $page) {
        $url = $xml->createElement('url');
        
        $loc = $xml->createElement('loc', $siteUrl . $page['loc']);
        $url->appendChild($loc);
        
        $lastmod = $xml->createElement('lastmod', date('Y-m-d'));
        $url->appendChild($lastmod);
        
        $changefreq = $xml->createElement('changefreq', $page['changefreq']);
        $url->appendChild($changefreq);
        
        $priority = $xml->createElement('priority', $page['priority']);
        $url->appendChild($priority);
        
        $urlset->appendChild($url);
    }
    
    // Dynamic pages from database (if tables exist)
    try {
        // Services
        $services = $database->query("SELECT slug, updated_at FROM services WHERE is_active = 1 ORDER BY id");
        foreach ($services as $service) {
            $url = $xml->createElement('url');
            
            $loc = $xml->createElement('loc', $siteUrl . '/services.html#' . $service['slug']);
            $url->appendChild($loc);
            
            $lastmod = $xml->createElement('lastmod', date('Y-m-d', strtotime($service['updated_at'])));
            $url->appendChild($lastmod);
            
            $changefreq = $xml->createElement('changefreq', 'weekly');
            $url->appendChild($changefreq);
            
            $priority = $xml->createElement('priority', '0.8');
            $url->appendChild($priority);
            
            $urlset->appendChild($url);
        }
        
        logMessage("Added " . count($services) . " services to sitemap", 'green');
        
        // Gallery items
        $galleryItems = $database->query("SELECT slug, updated_at FROM gallery WHERE is_active = 1 ORDER BY id");
        foreach ($galleryItems as $item) {
            $url = $xml->createElement('url');
            
            $loc = $xml->createElement('loc', $siteUrl . '/gallery.html#' . $item['slug']);
            $url->appendChild($loc);
            
            $lastmod = $xml->createElement('lastmod', date('Y-m-d', strtotime($item['updated_at'])));
            $url->appendChild($lastmod);
            
            $changefreq = $xml->createElement('changefreq', 'monthly');
            $url->appendChild($changefreq);
            
            $priority = $xml->createElement('priority', '0.6');
            $url->appendChild($priority);
            
            $urlset->appendChild($url);
        }
        
        logMessage("Added " . count($galleryItems) . " gallery items to sitemap", 'green');
        
    } catch (Exception $e) {
        logMessage("Note: Could not add dynamic content (database may not be set up yet)", 'yellow');
    }
    
    // Save sitemap
    $sitemapPath = __DIR__ . '/../public_html/sitemap-static.xml';
    if ($xml->save($sitemapPath)) {
        $fileSize = filesize($sitemapPath);
        logMessage("✓ Sitemap generated successfully: sitemap-static.xml (" . round($fileSize / 1024, 2) . " KB)", 'green');
        
        // Count URLs
        $urlCount = count($xml->getElementsByTagName('url'));
        logMessage("Total URLs: {$urlCount}", 'green');
    } else {
        logMessage("Failed to save sitemap", 'red');
        exit(1);
    }
    
    exit(0);
    
} catch (Exception $e) {
    logMessage("Error generating sitemap: " . $e->getMessage(), 'red');
    exit(1);
}
