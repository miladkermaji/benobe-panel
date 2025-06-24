<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalCenter extends Model
{
    protected $fillable = [
        'name',
        'title',
        'address',
        'secretary_phone',
        'phone_number',
        'postal_code',
        'province_id',
        'city_id',
        'is_main_center',
        'start_time',
        'end_time',
        'description',
        'latitude',
        'longitude',
        'consultation_fee',
        'payment_methods',
        'is_active',
        'working_days',
        'avatar',
        'documents',
        'phone_numbers',
        'location_confirmed',
        'type',
        'galleries',
        'specialty_ids',
        'insurance_ids',
    ];

    protected $casts = [
        'is_main_center'     => 'boolean',
        'is_active'          => 'boolean',
        'location_confirmed' => 'boolean',
        'working_days'       => 'array',
        'galleries'          => 'array',
        'documents'          => 'array',
        'phone_numbers'      => 'array',
        'specialty_ids'      => 'array',
        'insurance_ids'      => 'array',
        'consultation_fee'   => 'decimal:2',
        'latitude'           => 'decimal:7',
        'longitude'          => 'decimal:7',
    ];

public function doctors()
{
    return $this->belongsToMany(Doctor::class, 'doctor_medical_center');
}

    public function province()
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(Zone::class, 'city_id');
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'medical_center_specialty', 'medical_center_id', 'specialty_id');
    }

    public function insurances()
    {
        return $this->belongsToMany(Insurance::class, 'medical_center_insurance', 'medical_center_id', 'insurance_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
