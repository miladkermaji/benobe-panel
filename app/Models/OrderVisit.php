<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVisit extends Model
{
    protected $table = 'order_visits';

    protected $fillable = [
        'user_id',
        'doctor_id',
        'medical_center_id',
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
        'service_cost',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'appointment_date' => 'datetime',
        'amount' => 'integer',
        'visit_cost' => 'integer',
        'service_cost' => 'integer',
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
}
