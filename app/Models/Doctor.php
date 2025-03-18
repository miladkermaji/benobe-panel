<?php
namespace App\Models;

use App\Models\DoctorAppointmentConfig;
use App\Models\Doctors\DoctorManagement\DoctorTariff;
use App\Models\DoctorWorkSchedule;
use App\Models\Secretary;
use App\Models\Specialty;
use App\Models\UserDoctorLike;
use App\Models\Zone;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Morilog\Jalali\Jalalian;

class Doctor extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table    = "doctors";
    protected $slugable = 'slug';
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
        'api_token',
        'two_factor_secret', // فیلد برای ذخیره کلید مخفی
        'two_factor_enabled',
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
    ];
    protected $dates = ['created_at', 'updated_at', 'date_of_birth', 'mobile_verified_at', 'email_verified_at', 'last_login_at'];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['first_name', 'last_name'],
            ],
        ];
    }
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
        ? Storage::url($this->profile_photo_path)
        : asset('admin-assets/images/default-avatar.png');
    }
    public function getJalaliCreatedAtAttribute()
    {
        // چک کردن اینکه created_at مقدار داره یا نه
        if (! $this->created_at) {
            return '---'; // یا یه مقدار پیش‌فرض مثل '----/--/--'
        }

        return Jalalian::fromCarbon($this->created_at)->format('Y/m/d');
    }
    public function getFullNameAttribute()
    {
        return "$this->first_name $this->last_name";
    }
    public function clinics()
    {
        return $this->hasMany(Clinic::class);
    }
    public function province()
    {
        return $this->belongsTo(Zone::class, 'province_id');
    }
    public function tariff()
    {
        return $this->hasOne(DoctorTariff::class, 'doctor_id');
    }
    public function city()
    {
        return $this->belongsTo(Zone::class, 'city_id'); // ارتباط با شهر
    }
    public function academicDegree()
    {
        return $this->belongsTo(AcademicDegree::class, 'academic_degree_id');
    }
    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialty', 'doctor_id', 'specialty_id')
            ->withPivot('academic_degree_id', 'specialty_title'); // اضافه کردن فیلدهای اضافی
    }
    public function messengers()
    {
        return $this->hasMany(DoctorMessenger::class);
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
        $this->messengers->every(function ($messenger) {
            return $messenger->phone_number || $messenger->username;
        });
    }
    public function secretaries()
    {
        return $this->hasMany(Secretary::class, 'doctor_id');
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
            ! $this->messengers()->exists() || $this->messengers->contains(function ($messenger) {
                return ! $messenger->phone_number && ! $messenger->username;
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
        return $this->hasMany(DoctorWorkSchedule::class);
    }

    public function appointmentConfig()
    {
        return $this->hasOne(DoctorAppointmentConfig::class);
    }
}
