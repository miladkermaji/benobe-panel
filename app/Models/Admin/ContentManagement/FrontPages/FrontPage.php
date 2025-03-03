<?php

namespace App\Models\Admin\ContentManagement\FrontPages;

use Illuminate\Database\Eloquent\Model;

class FrontPage extends Model
{
    protected $fillable = [
        'page_url',
        'title',
        'image',
        'lead',
        'description',
        'approve',
        'tplname',
    ];
}
