<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'orderable_id', 'orderable_type', 'order_code', 'total_amount', 'status', 'order_date', 'notes',
    ];

    protected $casts = [
        'order_date' => 'date', // تبدیل به Carbon برای تاریخ
    ];

    public function orderable()
    {
        return $this->morphTo();
    }
}
