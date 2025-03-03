<?php

namespace App\Models\Admin\ContentManagement\Tags;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'usage_count', 'status'];
}
