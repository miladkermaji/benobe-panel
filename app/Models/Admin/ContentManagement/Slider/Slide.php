<?php

namespace App\Models\Admin\ContentManagement\Slider;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $table = 'slides';

    protected $fillable = [
        'title',
        'image',
        'link',
        'description',
        'display',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // متد برای گرفتن URL عکس
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/slides/' . $this->image) : null;
    }
}
