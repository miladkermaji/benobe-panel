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

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function secretary()
    {
        return $this->belongsTo(Secretary::class);
    }
    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class);
    }
}
