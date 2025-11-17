<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Services\JwtService;
use App\Models\AdminUser;

class AdminAuthMiddleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            ResponseHelper::unauthorized('Authentication token is required');
        }

        $container = Container::getInstance();
        $jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production';
        $jwtService = new JwtService($jwtSecret);

        $payload = $jwtService->verify($token);

        if (!$payload) {
            ResponseHelper::unauthorized('Invalid or expired token');
        }

        if (!isset($payload['user_id']) || $payload['type'] !== 'access') {
            ResponseHelper::unauthorized('Invalid token type');
        }

        // Load admin user
        $database = $container->get('database');
        $adminUserModel = new AdminUser($database);
        $adminUser = $adminUserModel->find($payload['user_id']);

        if (!$adminUser || $adminUser['status'] !== 'active') {
            ResponseHelper::unauthorized('User not found or inactive');
        }

        // Attach user to request for use in controllers
        $request->adminUser = $adminUser;

        return $next($request, $response);
    }
}
