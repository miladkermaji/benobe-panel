<?php

namespace App\Models\Admin\Dashboard\Membershipfee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MembershipFee extends Model
{
    use HasFactory;

    // تعیین جدول مرتبط
    protected $table = 'membership_fees';

    // فیلدهای قابل پر کردن
    protected $fillable = ['name', 'days', 'price', 'sort','status','user_type'];

    // مقادیر پیش‌فرض برای برخی فیلدها
    protected $attributes = [
        'sort' => 1, // مقدار پیش‌فرض برای ترتیب نمایش
    ];

    // **متد دسترسی به مقدار قیمت با فرمت تومان**
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, ',', '.') . ' تومان';
    }
}
