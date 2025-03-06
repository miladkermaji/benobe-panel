<?php

namespace App\Models\Admin\Panel\Tools;

use Illuminate\Database\Eloquent\Model;

class PageBuilderSetting extends Model
{
    protected $fillable = ['key', 'value'];
    protected $casts = [
        'value' => 'array',
    ];
}
