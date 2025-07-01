<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'name', 'url', 'icon', 'position', 'order', 'parent_id', 'status',
    ];

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }
}
