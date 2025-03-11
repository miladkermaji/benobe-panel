<?php
namespace App\Models;

use App\Models\Dr\Clinic;
use App\Models\Dr\Insurance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'clinic_id',
        'insurance_id',
        'duration',
        'consultation_type',
        'priority',
        'payment_status',
        'consultation_mode',
        'consultation_date',
        'start_time',
        'end_time',
        'reserved_at',
        'confirmed_at',
        'status',
        'attendance_status',
        'notes',
        'topic',
        'tracking_code',
        'fee',
        'notification_sent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'consultation_date' => 'date',
        'start_time'        => 'datetime:H:i:s',
        'end_time'          => 'datetime:H:i:s',
        'reserved_at'       => 'datetime',
        'confirmed_at'      => 'datetime',
        'notification_sent' => 'boolean',
    ];

    /**
     * Relationships
     */

    // ارتباط با دکتر
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // ارتباط با بیمار
    public function patient()
    {
        return $this->belongsTo(User::class);
    }

    // ارتباط با کلینیک
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    // ارتباط با بیمه
    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }
}
