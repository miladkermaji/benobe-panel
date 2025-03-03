<?php

namespace App\Models\Admin\Tools\Redirect;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['source_url', 'destination_url', 'status_code', 'is_active'];
}
