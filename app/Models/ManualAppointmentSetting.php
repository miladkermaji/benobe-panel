<?php

namespace App\Models;

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
        'medical_center_id',
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

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // بررسی یکتایی ترکیب doctor_id و medical_center_id (حتی وقتی medical_center_id نال است)
            $exists = ManualAppointmentSetting::where('doctor_id', $model->doctor_id)
                ->where('medical_center_id', $model->medical_center_id)
                ->when($model->exists, function ($query) use ($model) {
                    $query->where('id', '!=', $model->id);
                })
                ->exists();

            if ($exists) {
                throw new \Exception('این پزشک قبلاً برای این مرکز درمانی تنظیمات دارد.');
            }
        });
    }
}
