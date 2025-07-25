<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalCenter extends Model
{
    use SoftDeletes;
    use Sluggable;



    protected $fillable = [
        'name', 'title', 'address', 'secretary_phone', 'phone_number', 'postal_code',
        'siam_code',
        'province_id', 'city_id', 'is_main_center', 'start_time', 'end_time',
        'description', 'latitude', 'longitude', 'consultation_fee', 'payment_methods',
        'is_active', 'working_days', 'avatar', 'documents', 'phone_numbers',
        'location_confirmed', 'type', 'galleries', 'specialty_ids', 'insurance_ids',
        'service_ids', 'Center_tariff_type', 'Daycare_centers', 'slug', 'average_rating',
        'reviews_count', 'recommendation_percentage',
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
        'service_ids' => 'array',
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
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'id');
    }
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['name'],
            ],
        ];
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
        return Specialty::whereIn('id', $this->specialty_ids ?? [])->get();
    }

    public function insurances()
    {
        return Insurance::whereIn('id', $this->insurance_ids ?? [])->get();
    }

    public function services()
    {
        return Service::whereIn('id', $this->service_ids ?? [])->where('status', true)->get();
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
            $query->whereJsonContains('specialty_ids', array_map('intval', $specialtyIds));
        })->when($filters['insurance_ids'] ?? null, function ($query, $insuranceIds) {
            $query->whereJsonContains('insurance_ids', array_map('intval', $insuranceIds));
        })->when($filters['tariff_type'] ?? null, function ($query, $tariffType) {
            $query->where('Center_tariff_type', $tariffType);
        })->when($filters['service_ids'] ?? null, function ($query, $serviceIds) {
            $query->whereJsonContains('service_ids', array_map('intval', $serviceIds));
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
