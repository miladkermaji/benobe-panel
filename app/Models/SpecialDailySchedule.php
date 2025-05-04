<?php
namespace App\Models;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialDailySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'work_hours',
        'appointment_settings',
        'emergency_times',
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
