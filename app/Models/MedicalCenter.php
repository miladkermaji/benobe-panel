<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalCenter extends Model
{
    use SoftDeletes;

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
        'Center_tariff_type',
        'Daycare_centers',
        'slug',
        'average_rating',
        'reviews_count',
        'recommendation_percentage',
    ];

    protected $casts = [
        'is_main_center' => 'boolean',
        'is_active' => 'boolean',
        'location_confirmed' => 'boolean',
        'working_days' => 'array',
        'galleries' => 'array',
        'documents' => 'array',
        'phone_numbers' => 'array',
        'specialty_ids' => 'array',
        'insurance_ids' => 'array',
        'consultation_fee' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'average_rating' => 'decimal:1',
        'recommendation_percentage' => 'integer',
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
        return $this->belongsToMany(Insurance::class, 'medical_center_insurance', 'medical_center_id', 'insurance insurance_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['province_id'] ?? null, function ($query, $provinceId) {
            $query->where('province_id', $provinceId);
        })->when($filters['city_id'] ?? null, function ($query, $cityId) {
            $query->where('city_id', $cityId);
        })->when($filters['center_type'] ?? null, function ($query, $type) {
            $query->where('type', $type);
        })->when($filters['specialty_ids'] ?? null, function ($query, $specialtyIds) {
            $query->whereHas('specialties', function ($q) use ($specialtyIds) {
                $q->whereIn('specialties.id', $specialtyIds);
            });
        })->when($filters['insurance_ids'] ?? null, function ($query, $insuranceIds) {
            $query->whereHas('insurances', function ($q) use ($insuranceIds) {
                $q->whereIn('insurances.id', $insuranceIds);
            });
        })->when($filters['tariff_type'] ?? null, function ($query, $tariffType) {
            $query->where('Center_tariff_type', $tariffType);
        });
    }

    public function scopeSort($query, $sortBy, $sortDirection = 'desc')
    {
        if ($sortBy === 'average_rating') {
            return $query->orderBy('average_rating', $sortDirection);
        } elseif ($sortBy === 'reviews_count') {
            return $query->orderBy('reviews_count', $sortDirection);
        }
        return $query;
    }
}
