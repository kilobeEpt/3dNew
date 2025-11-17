<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;

class RateLimitMiddleware
{
    private const CACHE_DIR = __DIR__ . '/../../logs/rate_limit/';

    public function handle(Request $request, Response $response, callable $next)
    {
        $config = Container::getInstance()->get('config');
        $limit = $config->get('security.api_rate_limit', 100);
        
        $ip = $request->ip();
        $key = md5($ip);
        
        if (!$this->checkRateLimit($key, $limit)) {
            ResponseHelper::error('Too many requests. Please try again later.', null, 429);
        }

        return $next($request, $response);
    }

    private function checkRateLimit(string $key, int $limit): bool
    {
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }

        $file = self::CACHE_DIR . $key . '.json';
        $now = time();
        $window = 3600;

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            
            if ($now - $data['start'] > $window) {
                $data = ['start' => $now, 'count' => 1];
            } else {
                $data['count']++;
                
                if ($data['count'] > $limit) {
                    return false;
                }
            }
        } else {
            $data = ['start' => $now, 'count' => 1];
        }

        file_put_contents($file, json_encode($data));
        return true;
    }
}
