<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'national_code',
        'gender',
        'email',
        'password',
        'doctor_id',
        'clinic_id',
        'is_active',
        'profile_photo_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function permissions()
    {
        return $this->hasMany(SecretaryPermission::class);
    }
}
