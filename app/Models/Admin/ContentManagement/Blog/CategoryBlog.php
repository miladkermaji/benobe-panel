<?php

namespace App\Models\Admin\ContentManagement\Blog;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\ContentManagement\Blog\Blog;

class CategoryBlog extends Model
{
    protected $table = 'category_blogs';

    protected $fillable = [
        'name',
    ];

    public function blogs()
    {
        return $this->hasMany(Blog::class, 'category_id');
    }
}
