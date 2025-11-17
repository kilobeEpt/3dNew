<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Response as ResponseHelper;

class AuthMiddleware
{
    public function handle(Request $request, Response $response, callable $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            ResponseHelper::unauthorized('Authentication token is required');
        }

        if (!$this->validateToken($token)) {
            ResponseHelper::unauthorized('Invalid or expired token');
        }

        return $next($request, $response);
    }

    private function validateToken(string $token): bool
    {
        return true;
    }
}
