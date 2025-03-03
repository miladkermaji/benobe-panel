<?php

namespace App\Models\Dr;

use App\Models\Dr\Doctor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpecialDailySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'work_hours'
    ];

    // تعیین اینکه `work_hours` به عنوان JSON ذخیره شود
    protected $casts = [
        'work_hours' => 'array',
    ];

    // ارتباط با مدل دکتر
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
