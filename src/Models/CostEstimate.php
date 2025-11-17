<?php

declare(strict_types=1);

namespace App\Models;

class CostEstimate extends BaseModel
{
    protected string $table = 'cost_estimates';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'estimate_number',
        'customer_request_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'title',
        'description',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'valid_until',
        'notes',
        'terms_conditions',
        'created_by',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'file_path',
        'file_original_name',
        'file_size',
        'file_mime_type',
        'calculator_data',
        'source',
    ];

    protected array $casts = [
        'calculator_data' => 'json',
    ];
}
