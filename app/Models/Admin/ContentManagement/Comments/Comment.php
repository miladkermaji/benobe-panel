<?php

namespace App\Models\Admin\ContentManagement\Comments;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'name',
        'email',
        'ip',
        'comment',
        'reply',
        'approve',
    ];
}
