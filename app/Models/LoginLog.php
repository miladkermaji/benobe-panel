<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'secretary_id',
        'manager_id',
        'medical_center_id',
        'user_type',
        'login_at',
        'logout_at',
        'ip_address',
        'device',
        'login_method',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];
}
