<?php

namespace App\Models\Dr;

use App\Models\Dr\Clinic;
use App\Models\Dr\Doctor;
use App\Models\Dr\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'name',
        'calculation_method',
        'appointment_price',
        'insurance_percent',
        'final_price',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
