<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $fillable = ['name', 'address', 'phone', 'city', 'province', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
