<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterContent extends Model
{
    protected $table = 'footer_contents';

    protected $fillable = [
        'section',
        'title',
        'description',
        'link_url',
        'link_text',
        'icon_path',
        'image_path',
        'order',
        'is_active',
        'language',
        'extra_data',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'extra_data' => 'array', // تبدیل JSON به آرایه
    ];

    // رابطه برای گرفتن همه آیتم‌های یک بخش خاص
    public function scopeBySection($query, $section)
    {
        return $query->where('section', $section)->orderBy('order');
    }

    // فقط آیتم‌های فعال
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
