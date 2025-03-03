<?php

namespace App\Models\Admin\Doctors\DoctorManagements;

use App\Models\Dr\Doctor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
