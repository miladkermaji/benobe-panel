<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'reviewable_id',
        'reviewable_type',
        'name',
        'comment',
        'image_path',
        'rating',
        'is_approved',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_approved' => 'boolean',
    ];

    /**
     * رابطه Polymorphic با مدل‌های مختلف (User یا Doctor)
     */
    public function reviewable()
    {
        return $this->morphTo();
    }

    /**
     * گرفتن URL کامل تصویر
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
