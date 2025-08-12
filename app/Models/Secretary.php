<?php

namespace App\Models;

use App\Models\Doctor;
use App\Models\Manager;
use App\Models\Otp;
use App\Models\LoginAttempt;
use App\Models\LoginSession;
use App\Models\LoginLog;
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
        'static_password_enabled',
        'two_factor_secret',
        'two_factor_secret_enabled',
        'two_factor_confirmed_at',
        'is_active',
        'city_id',
        'province_id',
        'doctor_id',
        'medical_center_id',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'remember_token',
    ];

    protected $casts = [
        'static_password_enabled' => 'boolean',
        'two_factor_secret_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'is_active' => 'boolean',
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
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
    }

    public function clinic()
    {
        return $this->belongsTo(MedicalCenter::class, 'medical_center_id');
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

    public function otps()
    {
        return $this->morphMany(Otp::class, 'otpable');
    }

    public function loginAttempts()
    {
        return $this->morphMany(LoginAttempt::class, 'attemptable');
    }

    public function loginSessions()
    {
        return $this->morphMany(LoginSession::class, 'sessionable');
    }

    public function loginLogs()
    {
        return $this->morphMany(LoginLog::class, 'loggable');
    }

    // روابط استوری
    public function storyViews()
    {
        return $this->morphMany(StoryView::class, 'viewer');
    }

    public function storyLikes()
    {
        return $this->morphMany(StoryLike::class, 'liker');
    }
}
