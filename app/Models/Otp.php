<?php

namespace App\Models;

use App\Models\Admin\Manager;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;
    protected $table = "otps";

    protected $guarded = ['id'];

    // تبدیل به پولی مورفیک
    public function otpable()
    {
        return $this->morphTo();
    }
}
