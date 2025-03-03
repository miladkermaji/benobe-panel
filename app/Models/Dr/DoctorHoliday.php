<?php

namespace App\Models\Dr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorHoliday extends Model
{
    use HasFactory;

    protected $table = 'doctor_holidays'; // نام جدول
    protected $fillable = ['doctor_id', 'holiday_dates','clinic_id']; // ستون‌های قابل پر شدن
    protected $casts = [
        'holiday_dates' => 'array', // تبدیل به آرایه
    ];


    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
