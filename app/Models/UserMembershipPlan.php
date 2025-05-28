<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMembershipPlan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'duration_unit',
        'features',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'features' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }
}
