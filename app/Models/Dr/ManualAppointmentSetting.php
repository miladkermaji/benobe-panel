<?php

namespace App\Models\Dr;

use Illuminate\Database\Eloquent\Model;

class ManualAppointmentSetting extends Model
{
    protected $table = 'manual_appointment_settings';

    /**
     * فیلدهایی که به صورت انبوه قابل مقداردهی هستند.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'is_active',
        'duration_send_link',
        'duration_confirm_link',
    ];

    /**
     * رابطه با مدل پزشک
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
