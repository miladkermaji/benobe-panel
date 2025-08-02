<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalCenterSelectedDoctor extends Model
{
    protected $table = 'medical_center_selected_doctors';

    protected $fillable = [
        'medical_center_id',
        'doctor_id',
    ];

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
