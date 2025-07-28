<?php

namespace App\Models;

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
        'gender',
        'password',
        'is_active',
        'city_id',
        'province_id',
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
    public function subUsers()
    {
        return $this->morphMany(\App\Models\SubUser::class, 'owner');
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function province()
    {
        return $this->belongsTo(\App\Models\Zone::class, 'province_id');
    }

    public function city()
    {
        return $this->belongsTo(\App\Models\Zone::class, 'city_id');
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'clinic_id');
    }

    public function permissions()
    {
        return $this->hasMany(SecretaryPermission::class, 'secretary_id');
    }
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }

    public function prescriptions()
    {
        return $this->morphMany(PrescriptionRequest::class, 'requestable');
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
