<?php

namespace App\Models;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'doctor_id',
        'manager_id',
        'user_id',
        'message',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

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

    public function secretary()
    {
        return $this->belongsTo(\App\Models\Secretary::class);
    }
}
