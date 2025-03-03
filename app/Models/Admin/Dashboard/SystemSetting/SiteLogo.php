<?php

namespace App\Models\Admin\Dashboard\SystemSetting;

use Illuminate\Database\Eloquent\Model;

class SiteLogo extends Model
{
    protected $table = 'site_logos';

    protected $fillable = [
        'path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}