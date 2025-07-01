<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDoctorLike extends Model
{
    protected $fillable = ['likeable_id', 'likeable_type', 'doctor_id', 'liked_at'];

    protected $casts = [
        'liked_at' => 'datetime',
    ];

    public function likeable()
    {
        return $this->morphTo();
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
