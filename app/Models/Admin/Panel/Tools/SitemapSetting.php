<?php

namespace App\Models\Admin\Panel\Tools;

use Illuminate\Database\Eloquent\Model;

class SitemapSetting extends Model
{
    protected $fillable = [
        'base_url',
        'maximum_depth',
        'total_crawl_limit',
        'delay_between_requests',
        'ignore_robots',
    ];
}
