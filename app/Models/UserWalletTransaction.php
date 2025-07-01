<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWalletTransaction extends Model
{
    protected $fillable = [
        'walletable_id', 'walletable_type', 'amount', 'status', 'type', 'description', 'registered_at', 'paid_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'paid_at'       => 'datetime',
    ];

    public function walletable()
    {
        return $this->morphTo();
    }
}
