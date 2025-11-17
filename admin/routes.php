<?php

declare(strict_types=1);

use App\Controllers\Admin\DashboardController;

$router->get('/dashboard', DashboardController::class . '@index');
