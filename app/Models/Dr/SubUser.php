<?php

namespace App\Models\Dr;

use App\Models\User;
use App\Models\Dr\Doctor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubUser extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'user_id', 'status'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
