<?php

namespace App\Models\Dr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecretaryPermission extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'secretary_id','clinic_id', 'permissions', 'has_access'];

    protected $casts = [
        'permissions' => 'array', // این فیلد به صورت JSON ذخیره می‌شود
    ];

    // متد بررسی دسترسی
    public function hasPermission($key)
    {
        return in_array($key, $this->permissions ?? []);
    }
}
