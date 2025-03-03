<?php

namespace App\Models\Admin\ContentManagement\Blog;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\ContentManagement\Blog\CategoryBlog;

class Blog extends Model
{
    protected $table = 'blogs';

    protected $fillable = [
        'title',
        'category_id', // شناسه دسته‌بندی (فعلاً تک‌کتگوری فرض می‌کنیم)
        'date',
        'short_description',
        'content',
        'image',
        'views',
        'comments_count',
        'is_index', // انتشار در صفحه اصلی
        'status',
        'page_title',
        'url_seo',
        'meta_description',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_index' => 'boolean',
        'status' => 'boolean',
        'views' => 'integer',
        'comments_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // رابطه با دسته‌بندی
    public function category()
    {
        return $this->belongsTo(CategoryBlog::class, 'category_id');
    }

    // URL تصویر
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/blogs/' . $this->image) : null;
    }
}
