<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = "zone";
    protected $fillable = [
        'name',
        'parent_id',
        'level',
        'sort',
        'latitude',
        'longitude',
        'population',
        'area',
        'postal_code',
        'price_shipping',
        'status',
        'slug',
        'search_count',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'area' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Zone::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Zone::class, 'parent_id');
    }

    public function medicalCenters()
    {
        return $this->hasMany(MedicalCenter::class, 'province_id')
                    ->orWhere('city_id', $this->id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeProvinces($query)
    {
        return $query->where('level', 1)->active();
    }

    public function scopeCities($query)
    {
        return $query->where('level', 2)->active();
    }
}
