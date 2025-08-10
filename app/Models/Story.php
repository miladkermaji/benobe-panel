<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Story extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'media_path',
        'thumbnail_path',
        'status',
        'is_live',
        'live_start_time',
        'live_end_time',
        'duration',
        'views_count',
        'likes_count',
        'order',
        'metadata',
        'user_id',
        'doctor_id',
        'medical_center_id',
        'manager_id',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'live_start_time' => 'datetime',
        'live_end_time' => 'datetime',
        'metadata' => 'array',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'order' => 'integer',
    ];

    /**
     * روابط مدل
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class);
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }

    // روابط لایک و بازدید
    public function likes()
    {
        return $this->hasMany(StoryLike::class);
    }

    public function views()
    {
        return $this->hasMany(StoryView::class);
    }

    /**
     * Scope برای فیلتر کردن استوری‌های فعال
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope برای فیلتر کردن استوری‌های زنده
     */
    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }

    /**
     * Scope برای فیلتر کردن بر اساس نوع محتوا
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope برای مرتب کردن بر اساس ترتیب
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('created_at', 'desc');
    }

    /**
     * دریافت URL کامل فایل رسانه
     */
    public function getMediaUrlAttribute()
    {
        if ($this->media_path) {
            return Storage::url($this->media_path);
        }
        return null;
    }

    /**
     * دریافت URL کامل تصویر پیش‌نمایش
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return Storage::url($this->thumbnail_path);
        }
        return null;
    }

    /**
     * بررسی اینکه آیا استوری زنده است
     */
    public function isCurrentlyLive()
    {
        if (!$this->is_live) {
            return false;
        }

        $now = now();

        if ($this->live_start_time && $now < $this->live_start_time) {
            return false;
        }

        if ($this->live_end_time && $now > $this->live_end_time) {
            return false;
        }

        return true;
    }

    /**
     * افزایش تعداد بازدید
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * افزایش تعداد لایک
     */
    public function incrementLikes()
    {
        $this->increment('likes_count');
    }

    /**
     * کاهش تعداد لایک
     */
    public function decrementLikes()
    {
        $this->decrement('likes_count');
    }

    /**
     * دریافت نام صاحب استوری
     */
    public function getOwnerNameAttribute()
    {
        if ($this->doctor) {
            return $this->doctor->full_name;
        }

        if ($this->medicalCenter) {
            return $this->medicalCenter->name;
        }

        if ($this->manager) {
            return $this->manager->name;
        }

        if ($this->user) {
            return $this->user->name;
        }

        return 'نامشخص';
    }

    /**
     * دریافت نوع صاحب استوری
     */
    public function getOwnerTypeAttribute()
    {
        if ($this->doctor) {
            return 'doctor';
        }

        if ($this->medicalCenter) {
            return 'medical_center';
        }

        if ($this->manager) {
            return 'manager';
        }

        if ($this->user) {
            return 'user';
        }

        return null;
    }

    /**
     * دریافت آواتار صاحب استوری
     */
    public function getOwnerAvatarAttribute()
    {
        if ($this->doctor && $this->doctor->avatar) {
            return $this->doctor->avatar;
        }

        if ($this->medicalCenter && $this->medicalCenter->logo) {
            return $this->medicalCenter->logo;
        }

        if ($this->manager && $this->manager->avatar) {
            return $this->manager->avatar;
        }

        if ($this->user && $this->user->avatar) {
            return $this->user->avatar;
        }

        return null;
    }

    /**
     * حذف فایل‌های مرتبط هنگام حذف استوری
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($story) {
            // حذف فایل رسانه
            if ($story->media_path && Storage::exists($story->media_path)) {
                Storage::delete($story->media_path);
            }

            // حذف تصویر پیش‌نمایش
            if ($story->thumbnail_path && Storage::exists($story->thumbnail_path)) {
                Storage::delete($story->thumbnail_path);
            }
        });
    }
}
