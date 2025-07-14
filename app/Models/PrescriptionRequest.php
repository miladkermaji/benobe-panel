<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionRequest extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'type',
        'description',
        'tracking_code',
        'status',
        'insurance_id',
        'price',
        'payment_status',
    ];

    public function requestable()
    {
        return $this->morphTo();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }
}
