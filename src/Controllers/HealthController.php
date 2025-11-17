<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;

class HealthController
{
    public function check(Request $request, Response $response, array $params): void
    {
        $container = Container::getInstance();
        
        $status = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'services' => [
                'api' => 'up',
            ],
        ];

        if ($container->has('database')) {
            try {
                $db = $container->get('database');
                $db->getConnection();
                $status['services']['database'] = 'up';
            } catch (\Exception $e) {
                $status['services']['database'] = 'down';
                $status['status'] = 'degraded';
            }
        }

        $response->json($status);
    }
}
