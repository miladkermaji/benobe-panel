<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // رابطه با کاربران (فرض می‌کنیم جدول users وجود داره)
    public function users()
    {
        return $this->hasMany(User::class, 'group_id'); // فرض بر اینه که توی جدول users یه ستون group_id داری
    }

    // محاسبه تعداد کاربران به صورت دینامیک
    public function getUserCountAttribute()
    {
        return $this->users()->count();
    }
}
