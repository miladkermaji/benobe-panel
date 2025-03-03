<?php

namespace App\Models\Admin\ContentManagement\HomeVideo;

use Illuminate\Database\Eloquent\Model;

class HomeVideo extends Model
{
    protected $fillable = [
        'title',
        'image',
        'video',
        'description',
        'approve',
    ];
}
