<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'appointment_id',
        'userable_id',
        'userable_type',
        'comment',
        'status',
        'reply',
        'ip_address',
        'acquaintance',
        'overall_score',
        'recommend_doctor',
        'score_behavior',
        'score_explanation',
        'score_skill',
        'score_receptionist',
        'score_environment',
        'waiting_time',
        'visit_reason',
        'receptionist_comment',
        'experience_comment',
    ];

    protected $casts = [
        'status' => 'boolean',
        'recommend_doctor' => 'boolean',
        'overall_score' => 'integer',
        'score_behavior' => 'integer',
        'score_explanation' => 'integer',
        'score_skill' => 'integer',
        'score_receptionist' => 'integer',
        'score_environment' => 'integer',
        'appointment_id' => 'integer',
        'acquaintance' => 'string', // enum: other, friend, social, ads
    ];

    // رابطه polymorphic با کاربر (user, doctor, secretary, manager و ...)
    public function userable()
    {
        return $this->morphTo();
    }

    // رابطه با پزشک
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // رابطه با نوبت
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
