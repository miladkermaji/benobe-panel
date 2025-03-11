<?php
namespace App\Models;

use App\Models\Doctor;
use App\Models\Dr\Appointment;
use App\Models\Dr\Clinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
