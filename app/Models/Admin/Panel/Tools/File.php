<?php

namespace App\Models\Admin\Panel\Tools;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['name', 'path', 'type', 'extension', 'size'];
}
