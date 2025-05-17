<?php

namespace App\Models;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorSettlementRequest extends Model
{
    use SoftDeletes;
    protected $table = 'doctor_settlement_requests';
    protected $fillable = [
        'doctor_id',
        'amount',
        'status',
        'requested_at',
        'processed_at',
    ];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
