<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorNote extends Model
{
    protected $table = 'doctor_notes';

    protected $fillable = [
        'doctor_id', 'clinic_id', 'appointment_type', 'notes', 'status'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
