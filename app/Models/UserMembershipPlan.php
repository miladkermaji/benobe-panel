<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'price',
        'discount',
        'final_price',
        'duration_days',
        'duration_type',
        'appointment_count',
        'features',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'duration_days' => 'integer',
        'features' => 'array',
        'status' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }
}
