<?php

namespace App\Models\Admin\Agent;

use Illuminate\Database\Eloquent\Model;

class WalletReport extends Model
{
    protected $table = 'wallet_reports';

    protected $fillable = [
        'report_date',
        'description',
        'amount',
        'status',
    ];

    protected $casts = [
        'report_date' => 'datetime',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
