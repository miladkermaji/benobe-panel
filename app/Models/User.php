<?php
namespace App\Models;

use App\Models\Appointment;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use App\Models\Admin\Dashboard\Cities\Zone;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table    = "users";
    protected $fillable = [
        'first_name', 'last_name', 'email', 'mobile', 'password', 'national_code',
        'date_of_birth', 'sex', 'activation', 'profile_photo_path', 'zone_province_id', 'zone_city_id',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
        ? Storage::url($this->profile_photo_path)
        : asset('admin-assets/images/default-avatar.png');
    }

    public function province()
    {
        return $this->belongsTo(Zone::class, 'zone_province_id');
    }

    public function city()
    {
        return $this->belongsTo(Zone::class, 'zone_city_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            if ($user->profile_photo_path) {
                Storage::delete($user->profile_photo_path);
            }
        });
    }

    public function getFullNameAttribute()
    {
        return "$this->first_name . $this->last_name";
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }
}
