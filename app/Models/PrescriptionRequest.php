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
        'doctor_description',
        'tracking_code',
        'status',
        'prescription_insurance_id',
        'price',
        'payment_status',
        'clinic_id',
        'transaction_id',
        'referral_code',
    ];

    protected $casts = [
        'tracking_code' => 'integer',
        'price' => 'integer',
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

    public function insurances()
    {
        return $this->belongsToMany(\App\Models\PrescriptionInsurance::class, 'prescription_request_insurance', 'prescription_request_id', 'prescription_insurance_id')
            ->withPivot('referral_code')
            ->withTimestamps();
    }

    public function getReferralCodeFor($insuranceId)
    {
        $insurance = $this->insurances->firstWhere('id', $insuranceId);
        return $insurance ? $insurance->pivot->referral_code : null;
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function insulins()
    {
        return $this->belongsToMany(Insulin::class, 'prescription_insulin_request')
            ->withPivot('count');
    }
}
