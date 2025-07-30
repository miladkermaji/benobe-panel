<?php

namespace App\Models;

use App\Models\Zone;
use App\Models\SubUser;
use App\Models\Hospital;
use App\Models\DoctorTag;
use App\Models\Secretary;
use App\Models\Specialty;
use Morilog\Jalali\Jalalian;
use App\Models\Admin\Manager;
use App\Models\MedicalCenter;
use App\Models\DoctorDocument;
use App\Models\UserDoctorLike;
use Laravel\Sanctum\HasApiTokens;
use App\Models\DoctorWorkSchedule;
use Laravel\Jetstream\HasProfilePhoto;
use App\Models\DoctorAppointmentConfig;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Cviebrock\EloquentSluggable\Sluggable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Doctors\DoctorManagement\DoctorTariff;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Service;
use App\Models\PrescriptionRequest;

class Doctor extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Sluggable;

    protected $table    = "doctors";
    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'display_name',
        'date_of_birth',
        'sex',
        'mobile',
        'email',
        'alternative_mobile',
        'national_code',
        'password',
        'static_password_enabled',
        'license_number',
        'academic_degree_id',
        'specialty_id',
        'medical_system_code_type_id',
        'province_id',
        'city_id',
        'address',
        'postal_code',
        'slug',
        'profile_photo_path',
        'bio',
        'description',
        'is_active',
        'is_verified',
        'profile_completed',
        'status',
        'average_rating',
        'api_token',
        'two_factor_secret',
        'two_factor_secret_enabled',
        'views_count',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'remember_token',
        'api_token',
    ];

    protected $casts = [
        'date_of_birth'      => 'date',
        'is_active'          => 'boolean',
        'is_verified'        => 'boolean',
        'profile_completed'  => 'boolean',
        'mobile_verified_at' => 'datetime',
        'email_verified_at'  => 'datetime',
        'last_login_at'      => 'datetime',
        'permissions'        => 'array'
    ];

    protected $dates = ['created_at', 'updated_at', 'date_of_birth', 'mobile_verified_at', 'email_verified_at', 'last_login_at'];

    protected $appends = [
        'profile_photo_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($doctor) {
            $defaultPermissions = [
                "dashboard",
                "dr-panel",
                "appointments",
                "dr-appointments",
                "dr-workhours",
                "dr.panel.doctornotes.index",
                "dr-mySpecialDays",
                "dr-manual_nobat_setting",
                "dr-manual_nobat",
                "dr-scheduleSetting",
                "dr-vacation",
                "doctor-blocking-users.index",
                "consult",
                "dr-moshavere_setting",
                "dr-moshavere_waiting",
                "dr.panel.doctornotes.index",
                "dr-mySpecialDays-counseling",
                "consult-term.index",
                "insurance",
                "dr.panel.doctor-services.index",
                "financial_reports",
                "dr.panel.financial-reports.index",
                "dr-payment-setting",
                "dr-wallet-charge",
                "secretary_management",
                "dr-secretary-management",
                "clinic_management",
                "dr-clinic-management",
                "dr.panel.clinics.medical-documents",
                "doctors.clinic.deposit",
                "permissions",
                "dr-secretary-permissions",
                "profile",
                "dr-edit-profile",
                "dr-edit-profile-security",
                "dr-edit-profile-upgrade",
                "dr-my-performance",
                "dr-subuser",
                "my-dr-appointments",
                "statistics",
                "dr-my-performance-chart",
                "messages",
                "dr-panel-tickets",
                "#"
            ];

            // اگر دسترسی‌ای وجود نداشت، ایجاد کن
            if (!$doctor->permissions) {
                DoctorPermission::create([
                    'doctor_id' => $doctor->id,
                    'permissions' => $defaultPermissions
                ]);
            }
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['first_name', 'last_name'],
            ],
        ];
    }
    public function appointmentConfig()
    {
        return $this->hasOne(DoctorAppointmentConfig::class);
    }

    // رابطه جدید با doctor_counseling_configs (برای تنظیمات مشاوره)
    public function counselingConfig()
    {
        return $this->hasOne(DoctorCounselingConfig::class);
    }
    public function doctorHolidays()
    {
        return $this->hasMany(DoctorHoliday::class, 'doctor_id');
    }
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
        ? Storage::url($this->profile_photo_path)
        : asset('admin-assets/images/default-avatar.png');
    }public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactable');
    }
    public function selectedMedicalCenter()
    {
        return $this->hasOne(DoctorSelectedMedicalCenter::class, 'doctor_id');
    }

    /**
     * دریافت مرکز درمانی انتخاب‌شده یا مقدار پیش‌فرض (مشاوره آنلاین)
     */
    public function getCurrentMedicalCenterAttribute()
    {
        return $this->selectedMedicalCenter?->medicalCenter ?? null;
    }

    /**
     * تنظیم مرکز درمانی انتخاب‌شده
     */
    public function setSelectedMedicalCenter($medicalCenterId = null)
    {
        // همیشه رکورد را ایجاد یا به‌روزرسانی کن، حتی اگر medical_center_id null باشد
        $this->selectedMedicalCenter()->updateOrCreate(
            ['doctor_id' => $this->id],
            ['medical_center_id' => $medicalCenterId]
        );
    }
    public function getJalaliCreatedAtAttribute()
    {
        if (! $this->created_at) {
            return '---';
        }
        return Jalalian::fromCarbon($this->created_at)->format('Y/m/d');
    }

    public function getFullNameAttribute()
    {
        return "$this->first_name $this->last_name";
    }

    public function walletTransactions()
    {
        return $this->hasMany(DoctorWalletTransaction::class);
    }

    public function province()
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }



    public function city()
    {
        return $this->belongsTo(Zone::class, 'city_id');
    }
    // رابطه با کاربران زیرمجموعه
    public function subUsers()
    {
        return $this->morphMany(\App\Models\SubUser::class, 'owner');
    }
    public function academicDegree()
    {
        return $this->belongsTo(AcademicDegree::class, 'academic_degree_id');
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialty', 'doctor_id', 'specialty_id')
            ->withPivot('academic_degree_id', 'specialty_title');
    }

    public function messengers()
    {
        return $this->hasMany(DoctorMessenger::class);
    }

    public function faqs()
    {
        return $this->hasMany(DoctorFaq::class);
    }
    public function getSpecialtyNameAttribute()
    {
        return $this->specialty ? $this->specialty->name : 'نامشخص';
    }
    public function Specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function doctorSpecialties()
    {
        return $this->hasMany(DoctorSpecialty::class);
    }

    public function isProfileComplete(): bool
    {
        return $this->first_name &&
        $this->last_name &&
        $this->national_code &&
        $this->license_number &&
        $this->doctorSpecialties()->where('is_main', true)->exists() &&
        $this->uuid &&
        $this->messengers()->exists() &&
        $this->messengers->contains(function ($messenger) {
            return $messenger->phone_number || $messenger->username;
        });
    }

    public function secretaries()
    {
        return $this->hasMany(Secretary::class, 'doctor_id');
    }
    public function documents()
    {
        return $this->hasMany(DoctorDocument::class, 'doctor_id');
    }
    public function getIncompleteProfileSections(): array
    {
        $incompleteSections = [];

        if (! $this->first_name) {
            $incompleteSections[] = 'نام';
        }
        if (! $this->last_name) {
            $incompleteSections[] = 'نام خانوادگی';
        }
        if (! $this->national_code) {
            $incompleteSections[] = 'کد ملی';
        }
        if (! $this->license_number) {
            $incompleteSections[] = 'شماره نظام پزشکی';
        }
        if (! $this->doctorSpecialties()->where('is_main', true)->exists()) {
            $incompleteSections[] = 'تخصص و درجه علمی';
        }
        if (! $this->uuid) {
            $incompleteSections[] = 'آیدی';
        }
        if (
            ! $this->messengers()->exists() || ! $this->messengers->contains(function ($messenger) {
                return $messenger->phone_number || $messenger->username;
            })
        ) {
            $incompleteSections[] = 'پیام‌رسان‌ها';
        }

        return $incompleteSections;
    }

    public function likes()
    {
        return $this->hasMany(UserDoctorLike::class);
    }

    public function workSchedules()
    {
        return $this->hasMany(DoctorWorkSchedule::class, 'doctor_id')->where('is_working', true);
    }



    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function doctorTags()
    {
        return $this->hasMany(DoctorTag::class);
    }
    public function comments()
    {
        return $this->hasMany(DoctorComment::class);
    }
    public function depositSettings()
    {
        return $this->hasMany(MedicalCenterDepositSetting::class, 'doctor_id');
    }

    public function insurances()
    {
        return $this->belongsToMany(Insurance::class, 'doctor_insurance', 'doctor_id', 'insurance_id')
            ->withTimestamps();
    }
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function medicalCenters()
    {
        return $this->belongsToMany(MedicalCenter::class, 'doctor_medical_center', 'doctor_id', 'medical_center_id');
    }
    public function permissions()
    {
        return $this->hasOne(DoctorPermission::class);
    }

    public function getPermissionsAttribute()
    {
        if (!$this->permissions()->exists()) {
            $defaultPermissions = [
                "dashboard",
                "dr-panel",
                "appointments",
                "dr-appointments",
                "dr-workhours",
                "dr.panel.doctornotes.index",
                "dr-mySpecialDays",
                "dr-manual_nobat_setting",
                "dr-manual_nobat",
                "dr-scheduleSetting",
                "dr-vacation",
                "doctor-blocking-users.index",
                "consult",
                "dr-moshavere_setting",
                "dr-moshavere_waiting",
                "dr.panel.doctornotes.index",
                "dr-mySpecialDays-counseling",
                "consult-term.index",
                "insurance",
                "dr.panel.doctor-services.index",
                "financial_reports",
                "dr.panel.financial-reports.index",
                "dr-payment-setting",
                "dr-wallet-charge",
                "secretary_management",
                "dr-secretary-management",
                "clinic_management",
                "dr-clinic-management",
                "dr.panel.clinics.medical-documents",
                "doctors.clinic.deposit",
                "permissions",
                "dr-secretary-permissions",
                "profile",
                "dr-edit-profile",
                "dr-edit-profile-security",
                "dr-edit-profile-upgrade",
                "dr-my-performance",
                "dr-subuser",
                "my-dr-appointments",
                "statistics",
                "dr-my-performance-chart",
                "messages",
                "dr-panel-tickets",
                "#"
            ];

            DoctorPermission::create([
                'doctor_id' => $this->id,
                'permissions' => $defaultPermissions
            ]);
        }

        return $this->permissions()->first();
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'doctor_services', 'doctor_id', 'service_id')
            ->withPivot(['medical_center_id', 'insurance_id', 'name', 'description', 'status', 'duration', 'price', 'discount', 'parent_id']);
    }

    public function prescriptions()
    {
        return $this->morphMany(PrescriptionRequest::class, 'requestable');
    }

    /**
     * Compatibility: legacy code expects $doctor->clinics or $doctor->clinics()
     */
    public function clinics()
    {
        return $this->medicalCenters();
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
