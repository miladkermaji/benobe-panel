<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicDepositSetting extends Model
{
    use HasFactory;

    protected $table = 'clinic_deposit_settings';

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'deposit_amount',
        'notes',
        'is_active',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
