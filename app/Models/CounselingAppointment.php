<?php

namespace App\Models;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Insurance;
use Morilog\Jalali\Jalalian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselingAppointment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'counseling_appointments';

    // فیلدهای قابل پرکردن
    protected $fillable = [
          'doctor_id',
          'patient_id',
          'insurance_id',
          'medical_center_id',
          'actual_call_duration',
          'consultation_type',
          'priority',
          'payment_status',
          'appointment_type',
          'appointment_date',
          'appointment_time',
          'video_meeting_link',
          'chat_history',
          'reserved_at',
          'confirmed_at',
          'status',
          'attendance_status',
          'notes',
          'description',
          'title',
          'tracking_code',
          'max_appointments',
          'fee',
          'doctor_rating',
          'appointment_category',
          'location',
          'notification_sent',
          'call_recording_url',
      ];

    // نوع‌های داده‌ای که باید به صورت تاریخ شناخته شوند
    protected $casts = [
        'appointment_date' => 'date',           // تبدیل به Carbon برای تاریخ
        'appointment_time' => 'datetime:H:i:s', // تبدیل به Carbon برای زمان
        'reserved_at' => 'datetime', // اضافه کردن cast برای reserved_at
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
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

    public function getJalaliAppointmentDateAttribute()
    {
        return Jalalian::fromCarbon($this->appointment_date)->format('Y/m/d');
    }

    // Accessor برای وضعیت پرداخت (اگر وجود داشته باشه)
    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'unpaid' => 'پرداخت نشده',
            'failed' => 'ناموفق',
            default => 'نامشخص'
        };
    }
}
