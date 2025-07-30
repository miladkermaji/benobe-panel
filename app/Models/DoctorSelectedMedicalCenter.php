<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSelectedMedicalCenter extends Model
{
    protected $table = 'doctor_selected_medical_centers';

    protected $fillable = [
        'doctor_id',
        'medical_center_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
}
