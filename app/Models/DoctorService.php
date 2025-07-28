<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorService extends Model
{
    use HasFactory;
    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'insurance_id',
        'service_id',
        'name',
        'description',
        'status',
        'duration',
        'price',
        'discount',
        'parent_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'clinic_id');
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class, 'insurance_id');
    }

    public function parent()
    {
        return $this->belongsTo(DoctorService::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DoctorService::class, 'parent_id');
    }
}
