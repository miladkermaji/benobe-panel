<?php

namespace App\Models;

use App\Models\User;
use App\Models\Dr\Doctor;
use App\Models\Dr\Secretary;
use App\Models\Admin\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Otp extends Model
{
 use HasFactory;
 protected $table = "otps";



 protected $guarded = ['id'];


 public function doctor()
 {
  return $this->belongsTo(Doctor::class);
 }
 public function manager()
 {
  return $this->belongsTo(Manager::class);
 }
 public function user()
 {
  return $this->belongsTo(User::class);
 }
 public function secretary()
 {
  return $this->belongsTo(Secretary::class);
 }
}
