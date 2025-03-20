<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentCenterGallery extends Model
{
    protected $table = 'treatment_center_galleries';

    protected $fillable = [
        'treatment_center_id',
        'image_path',
        'caption',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function treatmentCenter(): BelongsTo
    {
        return $this->belongsTo(TreatmentCenter::class);
    }
}
