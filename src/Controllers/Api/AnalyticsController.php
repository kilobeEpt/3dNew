<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Models\AnalyticsEvent;

class AnalyticsController
{
    private AnalyticsEvent $model;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->model = new AnalyticsEvent($database);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $eventType = $request->input('event_type');
        $eventCategory = $request->input('event_category', 'general');
        $eventData = $request->input('event_data');

        if (empty($eventType)) {
            ResponseHelper::error('Event type is required', null, 400);
        }

        $userIp = $this->getClientIp($request);
        $userAgent = $request->getHeader('User-Agent') ?? '';

        try {
            $this->model->create([
                'event_type' => $eventType,
                'event_category' => $eventCategory,
                'event_data' => is_array($eventData) ? json_encode($eventData) : $eventData,
                'user_session_id' => $request->input('user_session_id'),
                'user_ip' => $userIp,
                'user_agent' => $userAgent,
                'page_url' => $request->input('page_url'),
                'referrer' => $request->input('referrer'),
            ]);

            ResponseHelper::success(null, 'Event logged');
        } catch (\Exception $e) {
            $container = Container::getInstance();
            $logger = $container->get('logger');
            $logger->error('Failed to log analytics event: ' . $e->getMessage());
            
            ResponseHelper::error('Failed to log event', null, 500);
        }
    }

    private function getClientIp(Request $request): ?string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $ip = $_SERVER[$header] ?? null;
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return null;
    }
}
