<?php

declare(strict_types=1);

namespace App\Models;

class PricingRule extends BaseModel
{
    protected string $table = 'pricing_rules';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'name',
        'description',
        'rule_type',
        'target_id',
        'condition_type',
        'condition_value',
        'discount_type',
        'discount_value',
        'priority',
        'is_active',
        'valid_from',
        'valid_to',
    ];
}
