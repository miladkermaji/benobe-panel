<?php

namespace App\Models\Dr;

use Illuminate\Database\Eloquent\Model;

class DoctorService extends Model
{
    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'name',
        'description',
        'duration',
        'price',
        'discount',
        'status',
        'parent_id',
    ];

    // ارتباط با مدل دکتر
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    // ارتباط خودارجاعی برای دسترسی به سرویس مادر (در صورت وجود)
    public function parent()
    {
        return $this->belongsTo(DoctorService::class, 'parent_id');
    }

    // ارتباط خودارجاعی برای دریافت زیرسرویس‌ها
    public function children()
    {
        return $this->hasMany(DoctorService::class, 'parent_id')->with('children');
    }


    public function subServices()
    {
        return $this->hasMany(DoctorService::class, 'parent_id');
    }
}
