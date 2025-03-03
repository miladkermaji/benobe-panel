<?php

namespace App\Models\Dr;

use App\Models\Dr\Doctor;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Dashboard\Cities\Zone;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Clinic extends Model
{
    use HasFactory;

    protected $table = 'clinics';

    protected $fillable = [
        'doctor_id',
        'name',
        'phone_numbers',
        'phone_number',
        'address',
        'province_id',
        'secretary_phone',
        'city_id',
        'postal_code',
        'description',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'is_active' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(Zone::class, 'city_id');
    }

    public function province()
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
