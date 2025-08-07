<?php

namespace App\Models;

use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insurance extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable = [
        'medical_center_id',
        'name',
        'slug',
        'calculation_method',
        'appointment_price',
        'insurance_percent',
        'final_price',
    ];
       /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
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
    public function medicalCenters()
    {
        return $this->belongsToMany(MedicalCenter::class, 'medical_center_insurance', 'insurance_id', 'medical_center_id');
    }

}
