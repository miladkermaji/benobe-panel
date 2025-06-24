<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DoctorMedicalCenter extends Pivot
{
    /**
     * نام جدول مرتبط
     *
     * @var string
     */
    protected $table = 'doctor_medical_center';

    /**
     * فیلدهای قابل پر شدن
     *
     * @var array
     */
    protected $fillable = [
        'medical_center_id',
        'doctor_id',
    ];

    /**
     * رابطه با مدل MedicalCenter
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }

    /**
     * رابطه با مدل Doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
