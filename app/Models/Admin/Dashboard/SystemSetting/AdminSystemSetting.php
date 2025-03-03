<?php

namespace App\Models\Admin\Dashboard\SystemSetting;

use Illuminate\Database\Eloquent\Model;

class AdminSystemSetting extends Model
{
    protected $table = 'admin_system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'integer':
                return (int) $setting->value;
            case 'boolean':
                return (bool) $setting->value;
            case 'json':
                return json_decode($setting->value, true);
            default:
                return $setting->value;
        }
    }

    public static function setValue($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        // فقط آپدیت می‌کنیم اگه وجود داره، نه ایجاد سطر جدید
        $setting = self::where('key', $key)->first();
        if ($setting) {
            $setting->update([
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]);
            return $setting;
        }
        // اگه وجود نداشت، یه سطر جدید می‌سازیم (ولی اینجا نباید برسه چون پیش‌فرض‌ها هستن)
        return self::create([
            'key' => $key,
            'value' => is_array($value) ? json_encode($value) : $value,
            'type' => $type,
            'group' => $group,
            'description' => $description,
        ]);
    }
}