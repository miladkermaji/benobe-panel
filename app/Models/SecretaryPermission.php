<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecretaryPermission extends Model
{
    protected $fillable = [
        'secretary_id',
        'doctor_id',
        'medical_center_id',
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

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
}
