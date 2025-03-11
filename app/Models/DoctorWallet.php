<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorWallet extends Model
{
    protected $fillable = ['doctor_id', 'balance'];
}
