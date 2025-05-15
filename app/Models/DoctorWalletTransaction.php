<?php

namespace App\Models;

use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorWalletTransaction extends Model
{
    use SoftDeletes;
    protected $table = 'doctor_wallet_transactions';

    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'amount',
        'status',
        'type',
        'description',
        'registered_at',
        'paid_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'paid_at'       => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
