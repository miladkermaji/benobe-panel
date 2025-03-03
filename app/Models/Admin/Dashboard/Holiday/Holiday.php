<?php

namespace App\Models\Admin\Dashboard\Holiday;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'title'];
}
