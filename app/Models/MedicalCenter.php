<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalCenter extends Model
{
    protected $fillable = ['name', 'status','description']; // فیلدها رو می‌تونی دستی تغییر بدی
}