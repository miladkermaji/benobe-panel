<?php

namespace App\Models;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;

class DoctorDocument extends Model
{
    protected $table = 'doctor_documents';

    protected $fillable = [
        'doctor_id',
        'file_path',
        'file_type',
        'title',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    // رابطه با پزشک
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
