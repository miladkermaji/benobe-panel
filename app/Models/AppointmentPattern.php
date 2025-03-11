<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentPattern extends Model
{
    protected $fillable = [
        'doctor_id', 'start_time', 'end_time', 'day', 'title', 'max_appointments', 'auto_schedule', 'include_holidays', 'disabled_days,is_active',
    ];
}
