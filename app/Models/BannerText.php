<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerText extends Model
{
    protected $fillable = ['main_text', 'switch_words', 'switch_interval', 'image_path', 'status'];

    protected $casts = [
        'switch_words' => 'array',
        'status'       => 'boolean',
    ];
}
