<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWalletTransaction extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'status', 'type', 'description', 'registered_at', 'paid_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'paid_at'       => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
