<?php

namespace App\Models\Dr;

use Illuminate\Database\Eloquent\Model;

class ClinicDepositSetting extends Model
{
    protected $fillable = [
        'clinic_id',
        'doctor_id',
        'deposit_amount',
        'is_custom_price',
    ];
}
