<?php

namespace App\Models;

use App\Models\Admin\Manager;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Secretary extends Authenticatable implements JWTSubject
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

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
