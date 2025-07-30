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
        // Manual appointment settings fields
        'is_active',
        'duration_send_link',
        'duration_confirm_link',
    ];

    protected $casts = [
        'auto_scheduling' => 'boolean',
        'online_consultation' => 'boolean',
        'holiday_availability' => 'boolean',
        'collaboration_with_other_sites' => 'boolean',
        'is_active' => 'boolean',
        'calendar_days' => 'integer',
        'appointment_duration' => 'integer',
        'duration_send_link' => 'integer',
        'duration_confirm_link' => 'integer',
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
