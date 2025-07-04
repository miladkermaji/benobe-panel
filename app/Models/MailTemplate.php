<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $fillable = [
        'subject',
        'template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
