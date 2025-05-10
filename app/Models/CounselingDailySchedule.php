<?php
namespace App\Models;

use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'appointment_settings',
        'emergency_times',
        'consultation_hours',
        'consultation_type',
    ];

    // نوع‌های داده‌ای که باید به صورت تاریخ شناخته شوند
    protected $dates = ['date'];

    // نوع داده‌های JSON
    protected $casts = [
        'consultation_hours' => 'array',
        'appointment_settings' => 'array',
        'emergency_times' => 'array',
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
