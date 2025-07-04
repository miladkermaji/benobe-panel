<?php

namespace App\Models;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalCenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BestDoctor extends Model
{
    protected $fillable = ['doctor_id', 'clinic_id', 'best_doctor', 'best_consultant', 'status', 'star_rating'];

    protected $casts = [
        'best_doctor'     => 'boolean',
        'best_consultant' => 'boolean',
        'status'          => 'boolean',
        'star_rating'     => 'decimal:1',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function appointments()
    {
        return $this->hasManyThrough(Appointment::class, Doctor::class, 'id', 'doctor_id', 'doctor_id', 'id');
    }

    public function hospital()
    {
        return $this->belongsTo(MedicalCenter::class, 'hospital_id')
            ->where('type', 'hospital');
    }
}
