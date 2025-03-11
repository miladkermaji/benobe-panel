<?php
namespace App\Models;

use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorProfileUpgrade extends Model
{
    use HasFactory;

    protected $table = 'doctor_profile_upgrades';

    protected $fillable = [
        'doctor_id',
        'payment_reference',
        'payment_status',
        'amount',
        'days',
        'paid_at',
        'expires_at',
    ];

    protected $dates = ['paid_at', 'expires_at'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function isActive()
    {
        return $this->payment_status === 'paid' && Carbon::now()->lt($this->expires_at);
    }
}
