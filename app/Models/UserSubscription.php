<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscribable_id',
        'subscribable_type',
        'plan_id',
        'transaction_id',
        'start_date',
        'end_date',
        'status',
        'description',
        'admin_id',
        'remaining_appointments'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function subscribable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan()
    {
        return $this->belongsTo(UserMembershipPlan::class, 'plan_id');
    }
}
