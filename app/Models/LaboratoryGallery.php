<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaboratoryGallery extends Model
{
    protected $table = 'laboratory_gallery';

    protected $fillable = [
        'laboratory_id', 'image_path', 'caption', 'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class, 'laboratory_id');
    }
}
