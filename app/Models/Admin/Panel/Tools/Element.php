<?php

namespace App\Models\Admin\Panel\Tools;

use App\Models\Admin\Panel\Tools\Page;
use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    protected $fillable = ['page_id', 'type', 'settings', 'content', 'order'];

    protected $casts = [
        'settings' => 'array', // این خط باعث می‌شه settings به‌صورت آرایه بارگذاری بشه
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
