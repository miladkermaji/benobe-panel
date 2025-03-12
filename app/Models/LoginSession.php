<?php

namespace App\Models;

use App\Models\Admin\Manager;
use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    protected $fillable = ['token', 'manager_id','user_id', 'secretary_id', 'doctor_id', 'step', 'expires_at'];
}
