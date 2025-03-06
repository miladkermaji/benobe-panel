<?php

namespace App\Models\Admin\Panel\Tools;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['name', 'structure', 'is_public'];

    protected $casts = [
        'structure' => 'array', // ساختار قالب به‌صورت JSON
    ];
}
