<?php

namespace Modules\Payment\App\Http\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
 use HasFactory;

 protected $fillable = [
  'user_id',
  'amount',
  'gateway',
  'status',
  'transaction_id',
  'meta'
 ];

 protected $casts = [
  'meta' => 'array',
 ];
}
