<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['source_url', 'target_url', 'is_active','status_code','description'];
}
