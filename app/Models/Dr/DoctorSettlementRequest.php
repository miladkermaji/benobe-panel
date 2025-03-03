<?php
namespace App\Models\Dr;

use App\Models\Dr\Doctor;
use Illuminate\Database\Eloquent\Model;

class DoctorSettlementRequest extends Model
{
    protected $table = 'doctor_settlement_requests';

    protected $fillable = [
        'doctor_id',
        'amount',
        'status',
        'requested_at',
        'processed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}