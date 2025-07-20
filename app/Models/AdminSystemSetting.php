<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSystemSetting extends Model
{
    protected $table = 'admin_system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];
}
