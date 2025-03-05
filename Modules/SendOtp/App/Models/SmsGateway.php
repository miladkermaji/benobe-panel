<?php

namespace Modules\SendOtp\App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
 protected $fillable = ['name', 'title', 'is_active', 'settings'];

 protected $casts = [
  'is_active' => 'boolean',
  'settings' => 'array',
 ];
}