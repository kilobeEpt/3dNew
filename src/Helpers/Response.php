<?php

declare(strict_types=1);

namespace App\Helpers;

class Response
{
    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function success($data = null, string $message = 'Success', int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(string $message = 'Error', $errors = null, int $statusCode = 400): void
    {
        self::json([
            'error' => true,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public static function created($data = null, string $message = 'Resource created'): void
    {
        self::success($data, $message, 201);
    }

    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, null, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, null, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, null, 403);
    }

    public static function validationError(array $errors, string $message = 'Validation failed'): void
    {
        self::error($message, $errors, 422);
    }
}
