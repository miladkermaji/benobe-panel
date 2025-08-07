<?php

namespace App\Models;

use App\Models\Doctor;
use App\Models\Otp;
use App\Models\LoginAttempt;
use App\Models\LoginSession;
use App\Models\LoginLog;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\ManagerPermission;

class Manager extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'managers';

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'national_code',
        'gender',
        'email',
        'mobile',
        'password',
        'two_factor_enabled',
        'static_password_enabled',
        'static_password',
        'bio',
        'address',
        'permission_level',
        'is_active',
        'is_verified',
        'profile_completed',
        'email_verified_at',
        'mobile_verified_at',
        'two_factor_confirmed_at',
        'two_factor_secret',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'static_password',
        'remember_token',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'mobile_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_secret' => 'string',
        'date_of_birth' => 'date',
        'two_factor_enabled' => 'boolean',
        'static_password_enabled' => 'boolean',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'profile_completed' => 'boolean',
        'permission_level' => 'integer',
    ];

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'manager_id');
    }

    public function prescriptions()
    {
        return $this->morphMany(\App\Models\PrescriptionRequest::class, 'requestable');
    }

    public function subUsers()
    {
        return $this->morphMany(\App\Models\SubUser::class, 'owner');
    }

    public function otps()
    {
        return $this->morphMany(\App\Models\Otp::class, 'otpable');
    }

    public function loginAttempts()
    {
        return $this->morphMany(\App\Models\LoginAttempt::class, 'attemptable');
    }

    public function loginSessions()
    {
        return $this->morphMany(\App\Models\LoginSession::class, 'sessionable');
    }

    public function loginLogs()
    {
        return $this->morphMany(\App\Models\LoginLog::class, 'loggable');
    }

    public function permissions()
    {
        return $this->hasOne(ManagerPermission::class);
    }

    public function getPermissionsAttribute()
    {
        if (!$this->permissions()->exists()) {
            $defaultPermissions = [
                "dashboard",
                "medical_centers",
                "support",
                "admin-panel",
                "user_management",
                "admin.panel.users.index",
                "admin.panel.user-groups.index",
                "admin.panel.user-blockings.index",
                "doctor_management",
                "admin.panel.doctors.index",
                "admin.panel.best-doctors.index",
                "admin.panel.doctor-documents.index",
                "admin.panel.doctor-specialties.index",
                "admin.panel.doctor-comments.index",
                "admin.panel.doctors.permissions",
                "membership",
                "admin.panel.user-subscriptions.index",
                "admin.panel.user-membership-plans.index",
                "admin.panel.user-appointment-fees.index",
                "patient_management",
                "admin.panel.users.index",
                "admin.panel.sub-users.index",
                "medical_centers",
                "admin.panel.hospitals.index",
                "admin.panel.laboratories.index",
                "admin.panel.clinics.index",
                "admin.panel.treatment-centers.index",
                "admin.panel.imaging-centers.index",
                "admin.panel.medical-centers.permissions",
                "service_management",
                "admin.panel.services.index",
                "admin.panel.doctor-services.index",
                "content_management",
                "admin.panel.blogs.index",
                "admin.panel.specialties.index",
                "admin.panel.zones.index",
                "admin.panel.reviews.index",
                "site_settings",
                "admin.panel.menus.index",
                "admin.panel.banner-texts.index",
                "admin.panel.footer-contents.index",
                "admin.panel.setting.index",
                "support",
                "admin.panel.tickets.index"
            ];

            ManagerPermission::create([
                'manager_id' => $this->id,
                'permissions' => $defaultPermissions
            ]);
        }

        return $this->permissions()->first();
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
