<?php

namespace App\Models\Admin\Doctors;

use Illuminate\Database\Eloquent\Model;

class OrderVisit extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_id',
        'clinic_id',
        'mobile',
        'payment_date',
        'bank_ref_id',
        'tracking_code',
        'payment_method',
        'amount',
        'appointment_date',
        'appointment_time',
        'center_name',
        'visit_cost',
        'service_cost'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'appointment_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Models\Dr\Doctor::class, 'doctor_id');
    }

    public function clinic()
    {
        return $this->belongsTo(\App\Models\Dr\Clinic::class, 'clinic_id');
    }
}
