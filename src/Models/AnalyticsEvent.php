<?php

declare(strict_types=1);

namespace App\Models;

class AnalyticsEvent extends BaseModel
{
    protected string $table = 'analytics_events';

    protected array $fillable = [
        'event_type',
        'event_category',
        'event_data',
        'user_session_id',
        'user_ip',
        'user_agent',
        'page_url',
        'referrer',
    ];

    protected array $casts = [
        'event_data' => 'json',
    ];
}
