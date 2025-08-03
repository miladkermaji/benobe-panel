<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'attemptable_type',
        'attemptable_id',
        'mobile',
        'attempts',
        'last_attempt_at',
        'lockout_until',
    ];

    // تبدیل به پولی مورفیک
    public function attemptable()
    {
        return $this->morphTo();
    }
}
