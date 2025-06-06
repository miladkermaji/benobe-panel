<?php

namespace Modules\Payment\App\Http\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function transactable()
    {
        return $this->morphTo();
    }
}
