<?php

namespace App\Models;

use App\Models\Manager;
use Illuminate\Database\Eloquent\Model;

class UserBlocking extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_id',
        'manager_id',
        'medical_center_id',
        'blocked_at',
        'unblocked_at',
        'reason',
        'status',
        'is_notified',
    ];

    // تعریف فیلدهای تاریخ برای تبدیل خودکار به Carbon
    protected $dates = [
        'blocked_at',
        'unblocked_at',
    ];

    // یا استفاده از casts در لاراول 9 و بالاتر
    protected $casts = [
        'blocked_at' => 'datetime',
        'unblocked_at' => 'datetime',
        'status' => 'boolean',
        'is_notified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }
}
