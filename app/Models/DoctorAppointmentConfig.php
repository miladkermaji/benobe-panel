<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorAppointmentConfig extends Model
{
    protected $fillable = [
        'doctor_id',
        'medical_center_id',
        'appointment_duration',
        'collaboration_with_other_sites',
        'auto_scheduling',
        'calendar_days',
        'online_consultation',
        'holiday_availability',
    ];

    public function setCalendarDaysAttribute($value)
    {
        $this->attributes['calendar_days'] = $value ?? 30;
    }

    // متد سفارشی برای بازگرداندن calendar_days
    public function getCalendarDaysAttribute($value)
    {
        return intval($value) ?: 30;
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
}
