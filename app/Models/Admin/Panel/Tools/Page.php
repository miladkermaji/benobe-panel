<?php

namespace App\Models\Admin\Panel\Tools;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Panel\Tools\Element;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'meta_title', 'meta_description', 'is_active', 'user_id'];

    public function elements()
    {
        return $this->hasMany(Element::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
