<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorNote extends Model
{
    protected $fillable = [
        'doctor_id',
        'medical_center_id',
        'appointment_type',
        'notes',
        'status',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
}
