<?php
namespace App\Models\Dr;

use Illuminate\Database\Eloquent\Model;

class DoctorWallet extends Model
{
    protected $fillable = ['doctor_id', 'balance'];
}