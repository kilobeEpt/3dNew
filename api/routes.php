<?php

declare(strict_types=1);

use App\Controllers\HealthController;

$router->get('/health', HealthController::class . '@check');
