<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorTag extends Model
{
    protected $table = 'doctor_tags'; // مشخص کردن نام جدول

    protected $fillable = ['doctor_id', 'name', 'color', 'text_color'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
