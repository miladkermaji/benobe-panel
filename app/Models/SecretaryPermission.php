<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecretaryPermission extends Model
{
    protected $fillable = [
        'secretary_id',
        'doctor_id',
        'clinic_id',
        'permissions',
        'has_access',
    ];

    protected $casts = [
        'permissions' => 'array',
        'has_access' => 'boolean',
    ];

    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
