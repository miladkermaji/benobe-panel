<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $fillable = [
        'doctor_id', 'name', 'address', 'secretary_phone', 'phone_number', 'postal_code',
        'province_id', 'city_id', 'is_main_center', 'start_time', 'end_time', 'description',
        'latitude', 'longitude', 'consultation_fee', 'payment_methods', 'is_active',
        'working_days', 'documents', 'phone_numbers', 'location_confirmed',
    ];

    protected $casts = [
        'is_main_center'     => 'boolean',
        'is_active'          => 'boolean',
        'location_confirmed' => 'boolean',
        'working_days'       => 'array',
        'documents'          => 'array',
        'phone_numbers'      => 'array',
        'consultation_fee'   => 'decimal:2',
        'latitude'           => 'decimal:7',
        'longitude'          => 'decimal:7',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function province()
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(Zone::class, 'city_id');
    }

    /**
     * رابطه با جدول laboratory_gallery
     */
    public function gallery()
    {
        return $this->hasMany(LaboratoryGallery::class, 'laboratory_id');
    }

    /**
     * گرفتن عکس اصلی گالری
     */
    public function getPrimaryImageAttribute()
    {
        return $this->gallery()->where('is_primary', true)->first()?->image_path ?? $this->gallery()->first()?->image_path ?? null;
    }
    public function galleries()
    {
        return $this->hasMany(LaboratoryGallery::class, 'laboratory_id');
    }
}
