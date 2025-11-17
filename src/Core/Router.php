<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    private array $groupMiddleware = [];
    private string $prefix = '';

    public function get(string $path, $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function options(string $path, $handler): self
    {
        return $this->addRoute('OPTIONS', $path, $handler);
    }

    public function any(string $path, $handler): self
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
        return $this;
    }

    private function addRoute(string $method, string $path, $handler): self
    {
        $fullPath = $this->prefix . $path;
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => array_merge($this->groupMiddleware, $this->middleware),
            'pattern' => $this->convertToPattern($fullPath),
        ];
        $this->middleware = [];
        return $this;
    }

    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->groupMiddleware;

        if (isset($attributes['prefix'])) {
            $this->prefix .= $attributes['prefix'];
        }

        if (isset($attributes['middleware'])) {
            $middleware = is_array($attributes['middleware']) 
                ? $attributes['middleware'] 
                : [$attributes['middleware']];
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    private function convertToPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                $request = new Request();
                $response = new Response();

                $next = function($request, $response) use ($route, $params) {
                    return $this->callHandler($route['handler'], $request, $response, $params);
                };

                foreach (array_reverse($route['middleware']) as $middlewareClass) {
                    $next = function($request, $response) use ($middlewareClass, $next) {
                        $middleware = new $middlewareClass();
                        return $middleware->handle($request, $response, $next);
                    };
                }

                $next($request, $response);
                return;
            }
        }

        http_response_code(404);
        if ($this->isApiRoute($uri)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => true, 'message' => 'Route not found']);
        } else {
            $this->serveFrontend();
        }
    }

    private function callHandler($handler, Request $request, Response $response, array $params)
    {
        if (is_callable($handler)) {
            return $handler($request, $response, $params);
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controllerClass, $method] = explode('@', $handler);
            $controller = new $controllerClass();
            return $controller->$method($request, $response, $params);
        }

        throw new \Exception('Invalid route handler');
    }

    private function isApiRoute(string $uri): bool
    {
        return strpos($uri, '/api/') === 0;
    }

    private function serveFrontend(): void
    {
        $frontendPath = __DIR__ . '/../../public_html/index.html';
        if (file_exists($frontendPath)) {
            readfile($frontendPath);
        } else {
            echo '404 - Page Not Found';
        }
    }
}
