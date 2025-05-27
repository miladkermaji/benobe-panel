<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Secretary extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'national_code',
        'gender',
        'email',
        'password',
        'doctor_id',
        'clinic_id',
        'is_active',
        'profile_photo_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function permissions()
    {
        return $this->hasMany(SecretaryPermission::class, 'secretary_id');
    }
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }
}
