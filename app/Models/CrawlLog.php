<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlLog extends Model
{
    protected $table = 'crawl_logs';

    protected $fillable = [
        'url',
        'status',
        'message',
    ];
}
