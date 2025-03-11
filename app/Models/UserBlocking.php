<?php
namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlocking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'clinic_id',
        'blocked_at',
        'unblocked_at',
        'reason',
        'status',
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
