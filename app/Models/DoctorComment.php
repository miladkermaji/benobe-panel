<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'user_name',
        'user_phone',
        'comment',
        'status',
        'reply',
        'ip_address',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // رابطه با پزشک
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
