<?php

namespace App\Models\Admin\Tools\MailTemplate;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $fillable = ['subject', 'template', 'is_active'];
}
