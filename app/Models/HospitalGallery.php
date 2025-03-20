<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalGallery extends Model
{
    protected $fillable = ['hospital_id', 'image_path', 'caption', 'is_primary'];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
