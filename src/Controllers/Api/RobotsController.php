<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;

class RobotsController
{
    private string $baseUrl;
    
    public function __construct()
    {
        $this->baseUrl = $_ENV['SITE_URL'] ?? 'https://example.com';
    }
    
    public function generate(Request $request, Response $response, array $params): void
    {
        $robots = $this->generateRobots();
        
        $response->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->send($robots);
    }
    
    private function generateRobots(): string
    {
        $lines = [];
        
        $lines[] = 'User-agent: *';
        $lines[] = 'Allow: /';
        $lines[] = '';
        
        $lines[] = 'Disallow: /api/';
        $lines[] = 'Disallow: /admin/';
        $lines[] = 'Disallow: /uploads/models/';
        $lines[] = 'Disallow: /logs/';
        $lines[] = 'Disallow: /src/';
        $lines[] = 'Disallow: /database/';
        $lines[] = 'Disallow: /templates/';
        $lines[] = '';
        
        $lines[] = 'Sitemap: ' . $this->baseUrl . '/sitemap.xml';
        $lines[] = '';
        
        return implode("\n", $lines);
    }
}
