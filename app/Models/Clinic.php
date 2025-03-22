<?php
namespace App\Models;

use App\Models\Doctor;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $table = 'clinics';

    protected $fillable = [
        'doctor_id',
        'name',
        'phone_numbers',
        'phone_number',
        'address',
        'province_id',
        'secretary_phone',
        'city_id',
        'postal_code',
        'description',
        'is_active', // Added from migration
        'is_main_clinic',
        'start_time',
        'end_time',
        'latitude',
        'longitude',
        'consultation_fee',
        'payment_methods',
        'working_days',
        'avatar', // JSON field, though we’ll use a separate table for galleries
        'documents',
        'location_confirmed',
    ];

    protected $casts = [
        'phone_numbers'      => 'array',
        'is_active'          => 'boolean',
        'is_main_clinic'     => 'boolean',
        'gallery'            => 'array',
        'documents'          => 'array',
        'working_days'       => 'array',
        'location_confirmed' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(Zone::class, 'city_id');
    }

    public function province()
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function galleries()
    {
        return $this->hasMany(ClinicGallery::class, 'clinic_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
