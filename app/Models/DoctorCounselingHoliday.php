<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorCounselingHoliday extends Model
{
   use HasFactory;

    protected $table = 'doctor_counseling_holidays';
    protected $fillable = ['doctor_id', 'clinic_id', 'holiday_dates', 'status'];
    protected $casts = [
        'holiday_dates' => 'array',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'clinic_id');
    }
    public function getHolidayDatesAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        return json_decode($value ?? '[]', true); // اگه JSON یا null باشه، به آرایه تبدیل می‌شه
    }
}