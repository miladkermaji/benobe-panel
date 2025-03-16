<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDoctorLike extends Model
{
    protected $fillable = ['user_id', 'doctor_id', 'liked_at'];

    protected $casts = [
        'liked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
