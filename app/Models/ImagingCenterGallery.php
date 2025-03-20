<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagingCenterGallery extends Model
{
    protected $table = 'imaging_center_galleries';

    protected $fillable = [
        'imaging_center_id',
        'image_path',
        'caption',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function imagingCenter()
    {
        return $this->belongsTo(ImagingCenter::class, 'imaging_center_id');
    }
}
