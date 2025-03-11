<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorHoliday extends Model
{
    use HasFactory;

    protected $table    = 'doctor_holidays';                           // نام جدول
    protected $fillable = ['doctor_id', 'holiday_dates', 'clinic_id']; // ستون‌های قابل پر شدن
    protected $casts    = [
        'holiday_dates' => 'array', // تبدیل به آرایه
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
