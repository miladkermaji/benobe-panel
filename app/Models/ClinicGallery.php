<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicGallery extends Model
{
    protected $table = 'clinic_galleries';

    protected $fillable = [
        'clinic_id',
        'image_path',
        'caption',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }
}
