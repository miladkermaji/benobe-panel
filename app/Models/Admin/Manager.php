<?php

namespace App\Models\Admin;

use App\Models\Doctor;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Manager extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'managers';

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'mobile_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        // 'two_factor_secret' رو یا حذف کن (چون رشته پیش‌فرضه) یا به 'string' تغییر بده
        'two_factor_secret' => 'string', // این بهتره برای وضوح
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
