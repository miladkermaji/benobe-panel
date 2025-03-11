<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecretaryPermission extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'secretary_id', 'clinic_id', 'permissions', 'has_access'];

    protected $casts = [
        'permissions' => 'array', // این فیلد به صورت JSON ذخیره می‌شود
    ];

    // متد بررسی دسترسی
    public function hasPermission($key)
    {
        return in_array($key, $this->permissions ?? []);
    }
}
