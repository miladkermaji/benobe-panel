<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSelectedClinic extends Model
{
    protected $table = 'doctor_selected_clinics';

    protected $fillable = [
        'doctor_id',
        'clinic_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
}
