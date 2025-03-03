<?php

namespace App\Models\Dr;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $table = "sms_templates";
    protected $fillable = ['doctor_id','identifier','title','content','type','user_id'];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
