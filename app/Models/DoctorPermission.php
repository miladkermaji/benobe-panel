<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorPermission extends Model
{
    protected $fillable = [
        'doctor_id',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
