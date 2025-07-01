<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    protected $fillable = ['walletable_id', 'walletable_type', 'balance'];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function walletable()
    {
        return $this->morphTo();
    }
}
