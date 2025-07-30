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

    // برای سازگاری با کد قدیمی که از medical_center_id استفاده می‌کرد
    public function getClinicIdAttribute()
    {
        return $this->medical_center_id;
    }

    public function setClinicIdAttribute($value)
    {
        $this->medical_center_id = $value;
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }

    // برای سازگاری با کد قدیمی
    public function clinic()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
}
