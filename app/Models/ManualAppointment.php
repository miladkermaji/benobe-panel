<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualAppointment extends Model
{
    use HasFactory;

    protected $table = 'manual_appointments'; // مشخص کردن نام جدول جدید

    protected $fillable = [
        'user_id',
        'doctor_id',
        'medical_center_id',
        'insurance_id',
        'appointment_date',
        'appointment_time',
        'description',
        'status',
        'payment_method',
        'payment_status',
        'tracking_code',
        'fee',
        'final_price',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }
}
