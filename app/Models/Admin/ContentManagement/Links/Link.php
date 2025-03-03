<?php

namespace App\Models\Admin\ContentManagement\Links;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\ContentManagement\Links\LinkCategory;

class Link extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'url',
        'rel',
        'approve',
    ];

    public function category()
    {
        return $this->belongsTo(LinkCategory::class, 'category_id');
    }
}
