<?php

declare(strict_types=1);

namespace App\Models;

class NewsPost extends BaseModel
{
    protected string $table = 'news_posts';
    protected bool $useSoftDeletes = true;
    
    protected array $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'category',
        'tags',
        'status',
        'is_featured',
        'view_count',
        'published_at',
        'meta_title',
        'meta_description',
    ];
}
