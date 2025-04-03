<?php

namespace App\Models;

use App\Models\Clinic;
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
    // در مدل ManualAppointmentSetting
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
    // در مدل ManualAppointmentSetting
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // بررسی یکتایی ترکیب doctor_id و clinic_id (حتی وقتی clinic_id نال است)
            $exists = ManualAppointmentSetting::where('doctor_id', $model->doctor_id)
                ->where('clinic_id', $model->clinic_id)
                ->when($model->exists, function ($query) use ($model) {
                    $query->where('id', '!=', $model->id);
                })
                ->exists();

            if ($exists) {
                throw new \Exception('این پزشک قبلاً برای این کلینیک تنظیمات دارد.');
            }
        });
    }
}
