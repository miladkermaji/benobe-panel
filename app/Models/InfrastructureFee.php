<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfrastructureFee extends Model
{
    protected $fillable = ['appointment_type', 'fee', 'is_active'];
}
