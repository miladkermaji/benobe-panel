<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class MedicalCenter extends Authenticatable
{
    use SoftDeletes;
    use Sluggable;
    use Notifiable;
    use HasApiTokens;

    protected $fillable = [
        'name', 'title', 'address', 'secretary_phone', 'phone_number', 'postal_code',
        'siam_code',
        'province_id', 'city_id', 'is_main_center', 'start_time', 'end_time',
        'description', 'latitude', 'longitude', 'consultation_fee', 'prescription_tariff', 'payment_methods',
        'is_active', 'working_days', 'avatar', 'documents', 'phone_numbers',
        'location_confirmed', 'type', 'galleries', 'specialty_ids', 'insurance_ids',
        'service_ids', 'Center_tariff_type', 'Daycare_centers', 'slug', 'average_rating',
        'reviews_count', 'recommendation_percentage', 'password', 'static_password_enabled', 'two_factor_secret_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
        'prescription_tariff' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'average_rating' => 'decimal:1',
        'recommendation_percentage' => 'integer',
        'static_password_enabled' => 'boolean',
        'two_factor_secret_enabled' => 'boolean',
    ];

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_medical_center', 'medical_center_id', 'doctor_id');
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

    // روابط جدید برای جایگزینی clinics
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'medical_center_id');
    }

    public function prescriptionRequests()
    {
        return $this->hasMany(PrescriptionRequest::class, 'medical_center_id');
    }

    public function doctorNotes()
    {
        return $this->hasMany(DoctorNote::class, 'medical_center_id');
    }

    public function vacations()
    {
        return $this->hasMany(Vacation::class, 'medical_center_id');
    }

    public function orderVisits()
    {
        return $this->hasMany(OrderVisit::class, 'medical_center_id');
    }

    public function counselingAppointments()
    {
        return $this->hasMany(CounselingAppointment::class, 'medical_center_id');
    }

    public function bestDoctors()
    {
        return $this->hasMany(BestDoctor::class, 'medical_center_id');
    }

    public function depositSettings()
    {
        return $this->hasMany(MedicalCenterDepositSetting::class, 'medical_center_id');
    }

    public function selectedByDoctors()
    {
        return $this->hasMany(DoctorSelectedMedicalCenter::class, 'medical_center_id');
    }

    public function selectedDoctor()
    {
        return $this->hasOne(MedicalCenterSelectedDoctor::class, 'medical_center_id');
    }

    /**
     * تنظیم پزشک انتخاب‌شده
     */
    public function setSelectedDoctor($doctorId = null)
    {
        $this->selectedDoctor()->updateOrCreate(
            ['medical_center_id' => $this->id],
            ['doctor_id' => $doctorId]
        );
    }
}
