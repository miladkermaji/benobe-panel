<?php
namespace App\Models;

use App\Models\Zone;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentCenter extends Model
{
    protected $table = 'treatment_centers';

    protected $fillable = [
        'doctor_id',
        'name',
        'address',
        'secretary_phone',
        'phone_number',
        'postal_code',
        'province_id',
        'city_id',
        'is_main_center',
        'start_time',
        'end_time',
        'description',
        'latitude',
        'longitude',
        'consultation_fee',
        'payment_methods',
        'is_active',
        'working_days',
        'avatar',
        'documents',
        'phone_numbers',
        'location_confirmed',
    ];

    protected $casts = [
        'is_main_center'     => 'boolean',
        'is_active'          => 'boolean',
        'location_confirmed' => 'boolean',
        'working_days'       => 'array', // تبدیل JSON به آرایه
        'gallery'            => 'array', // تبدیل JSON به آرایه
        'documents'          => 'array', // تبدیل JSON به آرایه
        'phone_numbers'      => 'array', // تبدیل JSON به آرایه
        'consultation_fee'   => 'decimal:2',
        'latitude'           => 'decimal:7',
        'longitude'          => 'decimal:7',
    ];

    /**
     * رابطه با جدول doctors
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * رابطه با جدول zone برای province
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }

    /**
     * رابطه با جدول zone برای city
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'city_id');
    }
     public function galleries()
    {
        return $this->hasMany(TreatmentCenterGallery::class);
    }
}
