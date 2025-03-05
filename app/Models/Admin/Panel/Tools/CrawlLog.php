<?php

namespace App\Models\Admin\Panel\Tools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CrawlLog extends Model
{
    use HasFactory;

    protected $table = 'crawl_logs'; // نام جدول

    protected $fillable = [
        'url',
        'status',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // اگر نیاز به رابطه خاصی داری، می‌تونی اینجا تعریف کنی
    // مثلاً رابطه با SitemapUrl (اختیاری)
    public function sitemapUrl()
    {
        return $this->hasOne(SitemapUrl::class, 'url', 'url');
    }
}
