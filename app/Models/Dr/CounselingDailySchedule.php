<?php

namespace App\Models\Dr;

use App\Models\Dr\Clinic;
use App\Models\Dr\Doctor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselingDailySchedule extends Model
{
    use HasFactory;

    protected $table = 'counseling_daily_schedules';

    // فیلدهای قابل پر کردن
    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'date',
        'consultation_hours',
        'consultation_type',
    ];

    // نوع‌های داده‌ای که باید به صورت تاریخ شناخته شوند
    protected $dates = ['date'];

    // نوع داده‌های JSON
    protected $casts = [
        'consultation_hours' => 'array',
    ];

    /**
     * رابطه با پزشک
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * رابطه با کلینیک
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * دامنه برای روزهای آینده
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    /**
     * دامنه برای برنامه‌های امروز
     */
    public function scopeToday($query)
    {
        return $query->where('date', now()->toDateString());
    }

    /**
     * دامنه برای پزشک خاص
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }
}
