<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Manager extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'managers';

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'mobile_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        // 'two_factor_secret' رو یا حذف کن (چون رشته پیش‌فرضه) یا به 'string' تغییر بده
        'two_factor_secret' => 'string', // این بهتره برای وضوح
    ];
}