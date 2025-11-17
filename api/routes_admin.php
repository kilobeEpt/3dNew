<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\ServiceCategoriesController;
use App\Controllers\Admin\ServicesController;
use App\Controllers\Admin\MaterialsController;
use App\Controllers\Admin\PricingRulesController;
use App\Controllers\Admin\GalleryController;
use App\Controllers\Admin\NewsController;
use App\Controllers\Admin\SiteSettingsController;
use App\Controllers\Admin\CustomerRequestsController;
use App\Controllers\Admin\CostEstimatesController;
use App\Controllers\Admin\AnalyticsController;
use App\Controllers\Admin\AuditLogsController;
use App\Middleware\AdminAuthMiddleware;

// Public auth routes
$router->post('/auth/login', AuthController::class . '@login');
$router->post('/auth/refresh', AuthController::class . '@refresh');
$router->post('/auth/request-password-reset', AuthController::class . '@requestPasswordReset');
$router->post('/auth/reset-password', AuthController::class . '@resetPassword');

// Protected auth routes
$router->get('/auth/me', AuthController::class . '@me')->middleware([AdminAuthMiddleware::class]);
$router->post('/auth/logout', AuthController::class . '@logout')->middleware([AdminAuthMiddleware::class]);

// Service Categories CRUD
$router->get('/service-categories', ServiceCategoriesController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/service-categories/{id}', ServiceCategoriesController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->post('/service-categories', ServiceCategoriesController::class . '@store')->middleware([AdminAuthMiddleware::class]);
$router->put('/service-categories/{id}', ServiceCategoriesController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->delete('/service-categories/{id}', ServiceCategoriesController::class . '@destroy')->middleware([AdminAuthMiddleware::class]);

// Services CRUD
$router->get('/services', ServicesController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/services/{id}', ServicesController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->post('/services', ServicesController::class . '@store')->middleware([AdminAuthMiddleware::class]);
$router->put('/services/{id}', ServicesController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->delete('/services/{id}', ServicesController::class . '@destroy')->middleware([AdminAuthMiddleware::class]);

// Materials CRUD
$router->get('/materials', MaterialsController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/materials/{id}', MaterialsController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->post('/materials', MaterialsController::class . '@store')->middleware([AdminAuthMiddleware::class]);
$router->put('/materials/{id}', MaterialsController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->delete('/materials/{id}', MaterialsController::class . '@destroy')->middleware([AdminAuthMiddleware::class]);

// Pricing Rules CRUD
$router->get('/pricing-rules', PricingRulesController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/pricing-rules/{id}', PricingRulesController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->post('/pricing-rules', PricingRulesController::class . '@store')->middleware([AdminAuthMiddleware::class]);
$router->put('/pricing-rules/{id}', PricingRulesController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->delete('/pricing-rules/{id}', PricingRulesController::class . '@destroy')->middleware([AdminAuthMiddleware::class]);

// Gallery CRUD
$router->get('/gallery', GalleryController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/gallery/{id}', GalleryController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->post('/gallery', GalleryController::class . '@store')->middleware([AdminAuthMiddleware::class]);
$router->put('/gallery/{id}', GalleryController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->delete('/gallery/{id}', GalleryController::class . '@destroy')->middleware([AdminAuthMiddleware::class]);

// News CRUD
$router->get('/news', NewsController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/news/{id}', NewsController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->post('/news', NewsController::class . '@store')->middleware([AdminAuthMiddleware::class]);
$router->put('/news/{id}', NewsController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->delete('/news/{id}', NewsController::class . '@destroy')->middleware([AdminAuthMiddleware::class]);

// Site Settings CRUD
$router->get('/settings', SiteSettingsController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/settings/{key}', SiteSettingsController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->post('/settings', SiteSettingsController::class . '@store')->middleware([AdminAuthMiddleware::class]);
$router->put('/settings/{key}', SiteSettingsController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->delete('/settings/{key}', SiteSettingsController::class . '@destroy')->middleware([AdminAuthMiddleware::class]);
$router->post('/settings/bulk', SiteSettingsController::class . '@bulk')->middleware([AdminAuthMiddleware::class]);

// Customer Requests Management
$router->get('/requests', CustomerRequestsController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/requests/{id}', CustomerRequestsController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->put('/requests/{id}', CustomerRequestsController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->post('/requests/{id}/assign', CustomerRequestsController::class . '@assign')->middleware([AdminAuthMiddleware::class]);
$router->post('/requests/{id}/status', CustomerRequestsController::class . '@updateStatus')->middleware([AdminAuthMiddleware::class]);
$router->get('/requests-stats', CustomerRequestsController::class . '@statistics')->middleware([AdminAuthMiddleware::class]);

// Cost Estimates CRUD
$router->get('/estimates', CostEstimatesController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/estimates/{id}', CostEstimatesController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->post('/estimates', CostEstimatesController::class . '@store')->middleware([AdminAuthMiddleware::class]);
$router->put('/estimates/{id}', CostEstimatesController::class . '@update')->middleware([AdminAuthMiddleware::class]);
$router->delete('/estimates/{id}', CostEstimatesController::class . '@destroy')->middleware([AdminAuthMiddleware::class]);
$router->post('/estimates/{id}/send', CostEstimatesController::class . '@send')->middleware([AdminAuthMiddleware::class]);

// Analytics
$router->get('/analytics/dashboard', AnalyticsController::class . '@dashboard')->middleware([AdminAuthMiddleware::class]);
$router->get('/analytics/popular-services', AnalyticsController::class . '@popularServices')->middleware([AdminAuthMiddleware::class]);
$router->get('/analytics/request-trends', AnalyticsController::class . '@requestTrends')->middleware([AdminAuthMiddleware::class]);
$router->get('/analytics/estimate-trends', AnalyticsController::class . '@estimateTrends')->middleware([AdminAuthMiddleware::class]);
$router->get('/analytics/recent-activity', AnalyticsController::class . '@recentActivity')->middleware([AdminAuthMiddleware::class]);
$router->get('/analytics/conversion-stats', AnalyticsController::class . '@conversionStats')->middleware([AdminAuthMiddleware::class]);
$router->get('/analytics/material-usage', AnalyticsController::class . '@materialUsage')->middleware([AdminAuthMiddleware::class]);
$router->get('/analytics/customer-stats', AnalyticsController::class . '@customerStats')->middleware([AdminAuthMiddleware::class]);

// Audit Logs
$router->get('/audit-logs', AuditLogsController::class . '@index')->middleware([AdminAuthMiddleware::class]);
$router->get('/audit-logs/{id}', AuditLogsController::class . '@show')->middleware([AdminAuthMiddleware::class]);
$router->get('/audit-logs/resource/{type}/{id}', AuditLogsController::class . '@byResource')->middleware([AdminAuthMiddleware::class]);
$router->get('/audit-logs/event-types', AuditLogsController::class . '@eventTypes')->middleware([AdminAuthMiddleware::class]);
$router->get('/audit-logs/auditable-types', AuditLogsController::class . '@auditableTypes')->middleware([AdminAuthMiddleware::class]);
