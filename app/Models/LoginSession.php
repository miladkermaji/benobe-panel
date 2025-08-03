<?php

namespace App\Models;

use App\Models\Manager;
use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    protected $fillable = [
        'token',
        'sessionable_type',
        'sessionable_id',
        'step',
        'expires_at'
    ];

    // تبدیل به پولی مورفیک
    public function sessionable()
    {
        return $this->morphTo();
    }
}
