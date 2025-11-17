<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Router;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;

$router = new Router();

$router->group(['prefix' => '/api', 'middleware' => [CorsMiddleware::class, RateLimitMiddleware::class]], function($router) {
    require __DIR__ . '/routes.php';
});

$router->dispatch();
