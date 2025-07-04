<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    protected $fillable = ['name', 'status','description']; // فیلدها رو می‌تونی دستی تغییر بدی
}
