<?php

namespace App\Models;

use Morilog\Jalali\Jalalian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transactable_id',
        'transactable_type',
        'amount',
        'gateway',
        'status',
        'transaction_id',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    // رابطه مورفیک
    public function transactable()
    {
        return $this->morphTo();
    }
    public function getJalaliCreatedAtAttribute()
    {
        return Jalalian::fromCarbon($this->created_at)->format('Y/m/d H:i');
    }
}
