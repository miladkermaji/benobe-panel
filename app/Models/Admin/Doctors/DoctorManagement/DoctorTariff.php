<?php
namespace App\Models\Doctors\DoctorManagement;

use Illuminate\Database\Eloquent\Model;

class DoctorTariff extends Model
{
    protected $fillable = ['doctor_id', 'visit_fee', 'site_fee'];

    public function doctor()
    {
        return $this->belongsTo(\App\Models\Doctor::class, 'doctor_id');
    }
}
