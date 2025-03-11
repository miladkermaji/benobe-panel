<?php
namespace App\Models;

use App\Models\Doctor;
use App\Models\Dr\Secretary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'secretary_id',
        'manager_id',
        'user_type',
        'login_at',
        'logout_at',
        'ip_address',
        'device',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
