<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\Response as ResponseHelper;

class CsrfMiddleware
{
    private const SESSION_KEY = 'csrf_token';

    public function handle(Request $request, Response $response, callable $next)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $method = $request->method();

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $origin = $request->header('Origin') ?: $request->header('Referer');
            $host = $request->header('Host');

            if ($origin && $host) {
                $originHost = parse_url($origin, PHP_URL_HOST);
                
                if ($originHost === $host) {
                    $token = $request->input('csrf_token') ?: $request->header('X-Csrf-Token');
                    
                    if (!$this->validateToken($token)) {
                        ResponseHelper::error('CSRF token validation failed', null, 403);
                    }
                }
            }
        }

        return $next($request, $response);
    }

    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    private function validateToken(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        $sessionToken = self::getToken();
        
        if (!$sessionToken) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}
