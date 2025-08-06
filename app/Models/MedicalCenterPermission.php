<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalCenterPermission extends Model
{
    protected $fillable = [
        'medical_center_id',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class);
    }
}
