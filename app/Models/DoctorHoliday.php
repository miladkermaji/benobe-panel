<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class DoctorHoliday extends Model
{
    use HasFactory;

    protected $table = 'doctor_holidays';
    protected $fillable = ['doctor_id', 'clinic_id', 'holiday_dates', 'status'];
    protected $casts = [
        'holiday_dates' => 'array',
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
