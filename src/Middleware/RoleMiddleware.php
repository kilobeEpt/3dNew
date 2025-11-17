<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Response as ResponseHelper;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = [])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request, Response $response, callable $next)
    {
        if (!isset($request->adminUser)) {
            ResponseHelper::unauthorized('User not authenticated');
        }

        $userRole = $request->adminUser['role'] ?? null;

        if (!$userRole || !in_array($userRole, $this->allowedRoles)) {
            ResponseHelper::forbidden('Insufficient permissions');
        }

        return $next($request, $response);
    }

    public static function only(array $roles): self
    {
        return new self($roles);
    }
}
