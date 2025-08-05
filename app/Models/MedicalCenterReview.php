<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCenterReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_center_id',
        'appointment_id',
        'userable_id',
        'userable_type',
        'comment',
        'status',
        'reply',
        'ip_address',
        'acquaintance',
        'overall_score',
        'recommend_center',
        'score_behavior',
        'score_cleanliness',
        'score_equipment',
        'score_receptionist',
        'score_environment',
        'waiting_time',
        'visit_reason',
        'receptionist_comment',
        'experience_comment',
    ];

    protected $casts = [
        'status' => 'boolean',
        'recommend_center' => 'boolean',
        'overall_score' => 'integer',
        'score_behavior' => 'integer',
        'score_cleanliness' => 'integer',
        'score_equipment' => 'integer',
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

    // رابطه با مرکز درمانی
    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class);
    }

    // رابطه با نوبت
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * گرفتن نام کاربر
     */
    public function getUserNameAttribute()
    {
        if ($this->userable) {
            if ($this->userable_type === User::class) {
                return $this->userable->name ?? 'کاربر';
            } elseif ($this->userable_type === Doctor::class) {
                return $this->userable->full_name ?? 'پزشک';
            }
        }
        return 'کاربر ناشناس';
    }

    /**
     * گرفتن امتیاز متوسط
     */
    public function getAverageScoreAttribute()
    {
        $scores = array_filter([
            $this->overall_score,
            $this->score_behavior,
            $this->score_cleanliness,
            $this->score_equipment,
            $this->score_receptionist,
            $this->score_environment,
        ]);

        return !empty($scores) ? round(array_sum($scores) / count($scores), 1) : 0;
    }
}
