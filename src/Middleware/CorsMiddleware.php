<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;

class CorsMiddleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        $config = Container::getInstance()->get('config');
        
        $allowedOrigins = $config->get('cors.allowed_origins', '*');
        $allowedMethods = $config->get('cors.allowed_methods', 'GET,POST,PUT,DELETE,OPTIONS');
        $allowedHeaders = $config->get('cors.allowed_headers', 'Content-Type,Authorization');

        header('Access-Control-Allow-Origin: ' . $allowedOrigins);
        header('Access-Control-Allow-Methods: ' . $allowedMethods);
        header('Access-Control-Allow-Headers: ' . $allowedHeaders);
        header('Access-Control-Max-Age: 86400');

        if ($request->method() === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        return $next($request, $response);
    }
}
