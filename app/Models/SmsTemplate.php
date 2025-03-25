<?php
namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    protected $table    = "sms_templates";
    protected $fillable = [
        'doctor_id',
        'user_id',
        'title',
        'content',
        'type',
        'recipient_type', // اضافه کردن این خط
        'identifier',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
