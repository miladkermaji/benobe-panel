<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BestDoctor extends Model
{
    protected $fillable = [
        'doctor_id',
        'medical_center_id',
        'best_doctor',
        'best_consultant',
        'star_rating',
        'status',
    ];

    protected $casts = [
        'best_doctor' => 'boolean',
        'best_consultant' => 'boolean',
        'star_rating' => 'decimal:1',
        'status' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
}
