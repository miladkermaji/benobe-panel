<?php
namespace App\Models;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
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
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_insurance', 'insurance_id', 'doctor_id')
                    ->withTimestamps();
    }
     public function doctorServices()
    {
        return $this->hasMany(DoctorService::class);
    }

}
