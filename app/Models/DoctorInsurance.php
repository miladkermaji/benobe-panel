<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorInsurance extends Model
{
    protected $fillable = ['doctor_id', 'insurance_id']; // فیلدها رو می‌تونی دستی تغییر بدی
}