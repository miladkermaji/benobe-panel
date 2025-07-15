<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitemapUrl extends Model
{
    protected $table = 'sitemap_urls';

    protected $fillable = [
        'url',
        'priority',
        'frequency',
        'type',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'float',
        'is_active' => 'boolean',
    ];
}
