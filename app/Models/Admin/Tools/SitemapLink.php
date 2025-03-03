<?php

namespace App\Models\Admin\Tools;

use Illuminate\Database\Eloquent\Model;

class SitemapLink extends Model
{
    protected $fillable = ['url', 'priority', 'changefreq', 'lastmod', 'is_active'];
}
