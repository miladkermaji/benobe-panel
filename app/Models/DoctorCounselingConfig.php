<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorCounselingConfig extends Model
{
    use HasFactory;

    protected $table = 'doctor_counseling_configs';

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'auto_scheduling',
        'calendar_days',
        'online_consultation',
        'holiday_availability',
        'appointment_duration',
        'has_phone_counseling',
        'has_text_counseling',
        'has_video_counseling',
        'collaboration_with_other_sites',
        'consultation_types',
        'price_15min',
        'price_30min',
        'price_45min',
        'price_60min',
        'working_days',
        'active',
    ];

    protected $casts = [
        'auto_scheduling'                => 'boolean',
        'online_consultation'            => 'boolean',
        'holiday_availability'           => 'boolean',
        'collaboration_with_other_sites' => 'boolean',
        'consultation_types'             => 'array',
        'working_days'                   => 'array',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
