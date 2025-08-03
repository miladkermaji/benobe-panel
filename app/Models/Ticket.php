<?php

namespace App\Models;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'user_id',
        'manager_id',
        'title',
        'description',
        'status',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function manager()
    {
        return $this->belongsTo(Manager::class, 'manager_id');
    }
    public function responses()
    {
        return $this->hasMany(TicketResponse::class);
    }
}
