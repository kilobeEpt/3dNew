<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Router;
use App\Middleware\AuthMiddleware;

session_start();

$router = new Router();

$router->group(['prefix' => '/admin', 'middleware' => [AuthMiddleware::class]], function($router) {
    require __DIR__ . '/routes.php';
});

$router->get('/admin/login', function($request, $response) {
    include __DIR__ . '/views/login.php';
});

$router->dispatch();
