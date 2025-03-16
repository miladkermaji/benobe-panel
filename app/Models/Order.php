<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_code', 'total_amount', 'status', 'order_date', 'notes',
    ];

    protected $casts = [
        'order_date' => 'date', // تبدیل به Carbon برای تاریخ
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
