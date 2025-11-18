<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Core\Database;
use App\Repositories\ServiceRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\NewsRepository;
use App\Repositories\GalleryRepository;

class SitemapController
{
    private Database $database;
    private string $baseUrl;
    
    public function __construct()
    {
        $container = Container::getInstance();
        $this->database = $container->get('database');
        $this->baseUrl = $_ENV['SITE_URL'] ?? 'https://example.com';
    }
    
    public function generate(Request $request, Response $response, array $params): void
    {
        $xml = $this->generateSitemap();
        
        $response->setHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->send($xml);
    }
    
    private function generateSitemap(): string
    {
        $urls = [];
        
        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/services.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/materials.html', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/gallery.html', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/news.html', 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => '/about.html', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/calculator.html', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => '/contact.html', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];
        
        foreach ($staticPages as $page) {
            $urls[] = $this->createUrlEntry(
                $page['loc'],
                null,
                $page['changefreq'],
                $page['priority']
            );
        }
        
        $serviceRepo = new ServiceRepository($this->database);
        $services = $serviceRepo->findAll(['status' => 'active']);
        foreach ($services as $service) {
            $urls[] = $this->createUrlEntry(
                '/services.html#service-' . $service['id'],
                $service['updated_at'] ?? $service['created_at'],
                'weekly',
                '0.8'
            );
        }
        
        $materialRepo = new MaterialRepository($this->database);
        $materials = $materialRepo->findAll(['status' => 'active']);
        foreach ($materials as $material) {
            $urls[] = $this->createUrlEntry(
                '/materials.html#material-' . $material['id'],
                $material['updated_at'] ?? $material['created_at'],
                'weekly',
                '0.7'
            );
        }
        
        $newsRepo = new NewsRepository($this->database);
        $newsPosts = $newsRepo->findAll(['status' => 'published'], ['created_at' => 'DESC'], 50);
        foreach ($newsPosts as $post) {
            $urls[] = $this->createUrlEntry(
                '/news.html#post-' . $post['id'],
                $post['updated_at'] ?? $post['created_at'],
                'monthly',
                '0.6'
            );
        }
        
        $galleryRepo = new GalleryRepository($this->database);
        $galleryItems = $galleryRepo->findAll(['status' => 'active'], ['created_at' => 'DESC'], 100);
        foreach ($galleryItems as $item) {
            $urls[] = $this->createUrlEntry(
                '/gallery.html#item-' . $item['id'],
                $item['updated_at'] ?? $item['created_at'],
                'monthly',
                '0.6'
            );
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= implode("\n", $urls);
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    private function createUrlEntry(
        string $loc,
        ?string $lastmod = null,
        string $changefreq = 'monthly',
        string $priority = '0.5'
    ): string {
        $url = $this->baseUrl . $loc;
        
        $xml = '  <url>' . "\n";
        $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
        
        if ($lastmod) {
            $date = date('Y-m-d', strtotime($lastmod));
            $xml .= '    <lastmod>' . $date . '</lastmod>' . "\n";
        }
        
        $xml .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
        $xml .= '    <priority>' . $priority . '</priority>' . "\n";
        $xml .= '  </url>';
        
        return $xml;
    }
}
