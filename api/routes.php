<?php

declare(strict_types=1);

use App\Controllers\HealthController;
use App\Controllers\Api\ServicesController;
use App\Controllers\Api\MaterialsController;
use App\Controllers\Api\PricingRulesController;
use App\Controllers\Api\GalleryController;
use App\Controllers\Api\NewsController;
use App\Controllers\Api\SettingsController;
use App\Controllers\Api\CostEstimatesController;
use App\Controllers\Api\ContactController;
use App\Controllers\Api\CsrfController;
use App\Controllers\Api\AnalyticsController;
use App\Middleware\CsrfMiddleware;

$router->get('/health', HealthController::class . '@check');
$router->get('/csrf-token', CsrfController::class . '@getToken');

$router->post('/analytics/events', AnalyticsController::class . '@store');

$router->get('/services', ServicesController::class . '@index');
$router->get('/services/{id}', ServicesController::class . '@show');

$router->get('/materials', MaterialsController::class . '@index');
$router->get('/materials/{id}', MaterialsController::class . '@show');
$router->get('/materials/categories', MaterialsController::class . '@categories');

$router->get('/pricing-rules', PricingRulesController::class . '@index');

$router->get('/gallery', GalleryController::class . '@index');
$router->get('/gallery/{id}', GalleryController::class . '@show');

$router->get('/news', NewsController::class . '@index');
$router->get('/news/{id}', NewsController::class . '@show');

$router->get('/settings', SettingsController::class . '@index');

$router->group(['middleware' => [CsrfMiddleware::class]], function($router) {
    $router->post('/cost-estimates', CostEstimatesController::class . '@store');
    $router->post('/contact', ContactController::class . '@store');
});

// Admin API routes
$router->group(['prefix' => '/admin'], function($router) {
    require __DIR__ . '/routes_admin.php';
});
