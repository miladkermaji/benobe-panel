<?php

namespace App\Models;

use App\Models\User;
use App\Models\Specialty;
use Morilog\Jalali\Jalalian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'doctor_id',
        'medical_center_id',
        'pattern_id',
        'insurance_id',
        'appointment_type',
        'appointment_date',
        'appointment_time',
        'reserved_at',
        'status',
        'notes',
        'description',
        'payment_method',
        'tracking_code',
        'fee',
        'final_price',
        'appointment_category',
        'location',
        'payment_status',
        'notification_sent',
        'include_holidays',
        'disabled_days',
        'patientable_id',
        'patientable_type',
    ];
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

    public function patientable()
    {
        return $this->morphTo();
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
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
            default => 'نامشخص'
        };
    }
}
