<?php

declare(strict_types=1);

namespace App\Core;

use App\Helpers\Response;

class ErrorHandler
{
    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
        return true;
    }

    public function handleException(\Throwable $e): void
    {
        $isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
        
        $logger = Container::getInstance()->get('logger');
        $logger->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        if (php_sapi_name() === 'cli') {
            echo "Error: " . $e->getMessage() . "\n";
            if ($isDebug) {
                echo $e->getTraceAsString() . "\n";
            }
            exit(1);
        }

        http_response_code(500);
        
        if ($this->isApiRequest()) {
            Response::json([
                'error' => true,
                'message' => $isDebug ? $e->getMessage() : 'Internal server error',
                'details' => $isDebug ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString()),
                ] : null,
            ], 500);
        } else {
            if ($isDebug) {
                echo "<h1>Error</h1>";
                echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
                echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            } else {
                echo "<h1>500 - Internal Server Error</h1>";
                echo "<p>Something went wrong. Please try again later.</p>";
            }
        }
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->handleException(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));
        }
    }

    private function isApiRequest(): bool
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($requestUri, '/api/') === 0 || 
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
