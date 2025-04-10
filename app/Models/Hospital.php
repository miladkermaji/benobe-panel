<?php

namespace App\Models;

use App\Models\Zone;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hospital extends Model
{
    use HasFactory;

    use Sluggable;
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['name','id'],
            ],
        ];
    }
    protected $fillable = [
        'doctor_id',
        'name',
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
    ];

    protected $casts = [
        'is_main_center'     => 'boolean',
        'is_active'          => 'boolean',
        'location_confirmed' => 'boolean',
        'working_days'       => 'array', // تبدیل JSON به آرایه
        'gallery'            => 'array', // تبدیل JSON به آرایه
        'documents'          => 'array', // تبدیل JSON به آرایه
        'phone_numbers'      => 'array', // تبدیل JSON به آرایه
        'consultation_fee'   => 'decimal:2',
        'latitude'           => 'decimal:7',
        'longitude'          => 'decimal:7',
    ];

    /**
     * رابطه با جدول doctors
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * رابطه با جدول zone برای province
     */
    public function province()
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }

    /**
     * رابطه با جدول zone برای city
     */
    public function city()
    {
        return $this->belongsTo(Zone::class, 'city_id');
    }

    public function bestDoctors()
    {
        return $this->hasMany(BestDoctor::class);
    }
    public function galleries()
    {
        return $this->hasMany(HospitalGallery::class);
    }
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'hospital_id');
    }
}
