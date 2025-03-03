<?php

namespace App\Models\Dr;

use App\Models\Dr\SecretaryPermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Secretary extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'national_code',
        'gender',
        'address',
        'birth_date',
        'profile_photo_path',
        'password',
        'is_active',
    ];

    // کست کردن فیلدها
    protected $hidden = ['password'];
    protected $casts = [
        'is_active' => 'boolean',
        'birth_date' => 'date',
    ];

    // ارتباط با مدل دکتر
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function permissions()
    {
        return $this->hasOne(SecretaryPermission::class, 'secretary_id', 'id');
    }
}
