<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class DoctorHoliday extends Model
{
    use HasFactory;

    protected $table = 'doctor_holidays';
    protected $fillable = ['doctor_id', 'medical_center_id', 'holiday_dates', 'status'];
    protected $casts = [
        'holiday_dates' => 'array',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
    public function getHolidayDatesAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        return json_decode($value ?? '[]', true); // اگه JSON یا null باشه، به آرایه تبدیل می‌شه
    }

}
