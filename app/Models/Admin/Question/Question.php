<?php

namespace App\Models\Admin\Question;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Question\QuestionCategory;

class Question extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'question',
        'asker_name',
        'asker_phone',
        'reply',
        'replier_name',
        'approve',
    ];

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id');
    }
}
