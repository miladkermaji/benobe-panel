<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'is_active',
        'user_id',
    ];

    public function elements(): HasMany
    {
        return $this->hasMany(Element::class);
    }
}
