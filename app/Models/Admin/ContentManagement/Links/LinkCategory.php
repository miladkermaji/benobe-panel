<?php

namespace App\Models\Admin\ContentManagement\Links;

use Illuminate\Database\Eloquent\Model;

class LinkCategory extends Model
{
    protected $fillable = ['name'];

    public function links()
    {
        return $this->hasMany(Link::class, 'category_id');
    }
}
