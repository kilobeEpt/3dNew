<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;

class DashboardController
{
    public function index(Request $request, Response $response, array $params): void
    {
        include __DIR__ . '/../../../admin/views/dashboard.php';
    }
}
