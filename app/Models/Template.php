<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name',
        'structure',
        'is_public',
    ];

    protected $casts = [
        'structure' => 'array',
        'is_public' => 'boolean',
    ];
}
