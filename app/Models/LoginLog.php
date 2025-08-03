<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'user_type',
        'login_at',
        'logout_at',
        'ip_address',
        'device',
        'login_method',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    /**
     * Get the parent loggable model (doctor, secretary, user, manager, medical_center).
     */
    public function loggable()
    {
        return $this->morphTo();
    }

    /**
     * Scope for filtering by user type
     */
    public function scopeByUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope for filtering by loggable type
     */
    public function scopeByLoggableType($query, $loggableType)
    {
        return $query->where('loggable_type', $loggableType);
    }
}
