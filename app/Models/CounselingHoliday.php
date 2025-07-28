<?php

namespace App\Models;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounselingHoliday extends Model
{
    use HasFactory;

    protected $table = 'counseling_holidays';

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'holiday_dates',
        'status',
    ];

    // تبدیل فیلد JSON به آرایه در هنگام استفاده
    protected $casts = [
        'holiday_dates' => 'array',
    ];

    /**
     * رابطه با پزشک
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * رابطه با مرکز درمانی
     */
    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'clinic_id');
    }

    /**
     * دامنه برای تعطیلات فعال
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * دامنه برای دریافت تعطیلات پزشک خاص
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }
}
