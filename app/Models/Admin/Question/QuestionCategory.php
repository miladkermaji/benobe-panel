<?php

namespace App\Models\Admin\Question;

use App\Models\Admin\Question\Question;
use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    protected $fillable = ['name', 'alt_name', 'approve'];

    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id');
    }
}
