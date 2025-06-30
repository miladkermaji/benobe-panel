<?php

namespace App\Models;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Admin\Manager;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Secretary extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'national_code',
        'email',
        'password',
        'is_active',
        'doctor_id',
        'clinic_id',
    ];

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
}
