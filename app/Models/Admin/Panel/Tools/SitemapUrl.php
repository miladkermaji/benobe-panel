<?php

namespace App\Models\Admin\Panel\Tools;

use Illuminate\Database\Eloquent\Model;

class SitemapUrl extends Model
{
    protected $fillable = ['url', 'priority', 'frequency', 'type', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'float',
    ];
}
