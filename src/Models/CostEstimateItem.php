<?php

declare(strict_types=1);

namespace App\Models;

class CostEstimateItem extends BaseModel
{
    protected string $table = 'cost_estimate_items';
    protected bool $useSoftDeletes = false;
    
    protected array $fillable = [
        'estimate_id',
        'item_type',
        'item_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'line_total',
        'display_order',
    ];
}
