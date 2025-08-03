<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicDepositSetting extends Model
{
    protected $table = 'medical_center_deposit_settings';

    protected $fillable = [
        'doctor_id',
        'medical_center_id',
        'deposit_amount',
        'is_custom_price',
        'refundable',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'is_custom_price' => 'boolean',
        'refundable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
}
