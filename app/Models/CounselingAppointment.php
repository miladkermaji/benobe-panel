<?php
namespace App\Models;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Insurance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CounselingAppointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'counseling_appointments';

    // فیلدهای قابل پرکردن
  protected $fillable = [
        'doctor_id',
        'patient_id',
        'insurance_id',
        'clinic_id',
        'duration',
        'actual_call_duration',
        'consultation_type',
        'priority',
        'payment_status',
        'appointment_type',
        'appointment_date',
        'start_time',
        'end_time',
        'video_meeting_link',
        'chat_history',
        'reserved_at',
        'confirmed_at',
        'status',
        'attendance_status',
        'notes',
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
    protected $dates = [
        'appointment_date',
        'reserved_at',
        'confirmed_at',
        'deleted_at',
    ];

    // رابطه با پزشک
   public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id')
            ->with(['specialty']);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class, 'insurance_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    /**
     * وضعیت پرداخت خوانا
     */
    public function getPaymentStatusLabelAttribute()
    {
        return match ($this->payment_status) {
            'pending'   => 'در انتظار پرداخت',
            'paid'      => 'پرداخت‌شده',
            'unpaid'    => 'پرداخت‌نشده',
            default     => 'نامشخص',
        };
    }

    /**
     * وضعیت مشاوره خوانا
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'scheduled' => 'زمان‌بندی‌شده',
            'cancelled' => 'لغوشده',
            'attended'  => 'حضور یافته',
            'missed'    => 'غایب',
            default     => 'نامشخص',
        };
    }

    /**
     * دامنه عمومی برای مشاوره‌های فعال
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * دامنه عمومی برای مشاوره‌های امروز
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', now()->toDateString());
    }

    /**
     * دامنه عمومی برای مشاوره‌های آینده
     */
    public function scopeUpcoming($query)
    {
        return $query->whereDate('appointment_date', '>', now()->toDateString());
    }
}
