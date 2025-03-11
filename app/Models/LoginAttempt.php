<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'secretary_id',
        'manager_id',
        'mobile',
        'attempts',
        'last_attempt_at',
        'lockout_until',
    ];

}
