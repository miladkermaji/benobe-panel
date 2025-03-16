<?php
namespace App\Models;

use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'pattern_id',
        'insurance_id',
        'appointment_type',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'tracking_code',
        'fee',
        'appointment_category',
        'location',
        'notification_sent',
        'include_holidays',
        'disabled_days',
    ];
protected $casts = [
        'appointment_date' => 'date', // تبدیل به Carbon برای تاریخ
        'start_time' => 'datetime:H:i:s', // تبدیل به Carbon برای زمان
    ];
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }
    public function pattern()
    {
        return $this->belongsTo(AppointmentPattern::class);
    }
}
