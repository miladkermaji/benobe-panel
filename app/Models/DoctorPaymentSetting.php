<?php
namespace App\Models;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;

class DoctorPaymentSetting extends Model
{
    protected $table = 'doctor_payment_settings';

    protected $fillable = [
        'doctor_id',
        'visit_fee',
        'card_number',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
