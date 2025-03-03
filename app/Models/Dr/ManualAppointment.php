<?php

namespace App\Models\Dr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManualAppointment extends Model
{
    use HasFactory;

    protected $table = 'manual_appointments'; // مشخص کردن نام جدول جدید

    protected $fillable = [
        'user_id',
        'doctor_id',
        'clinic_id',
        'appointment_date',
        'appointment_time',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
