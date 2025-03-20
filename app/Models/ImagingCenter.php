<?php
namespace App\Models;

use App\Models\Doctor;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagingCenter extends Model
{
    protected $table = 'imaging_centers';

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
        'service_fee',
        'payment_methods',
        'is_active',
        'working_days',
        'gallery',
        'documents',
        'phone_numbers',
        'location_confirmed',
    ];

    protected $casts = [
        'is_main_center'     => 'boolean',
        'is_active'          => 'boolean',
        'location_confirmed' => 'boolean',
        'working_days'       => 'array',
        'gallery'            => 'array',
        'documents'          => 'array',
        'phone_numbers'      => 'array',
        'service_fee'        => 'decimal:2',
        'latitude'           => 'decimal:7',
        'longitude'          => 'decimal:7',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'city_id');
    }

    public function galleries()
    {
        return $this->hasMany(ImagingCenterGallery::class, 'imaging_center_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
