<?php

namespace App\Models\Admin\Tools;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = ['email', 'is_active'];
}
