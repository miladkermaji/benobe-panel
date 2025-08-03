<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitemapSetting extends Model
{
    use HasFactory;

    protected $table = 'sitemap_settings';

    protected $fillable = [
        'base_url',
        'maximum_depth',
        'total_crawl_limit',
        'delay_between_requests',
        'ignore_robots',
    ];
}
