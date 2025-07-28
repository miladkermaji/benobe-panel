<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated This model is deprecated. Use MedicalCenter instead.
 * This is a temporary compatibility layer during migration from clinics to medical_centers.
 */
class Clinic extends Model
{
    protected $table = 'medical_centers';

    // این مدل فقط برای سازگاری موقت است
    // در آینده باید تمام کدها به MedicalCenter تغییر کنند

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Log warning for deprecated usage
        Log::warning('Clinic model is deprecated. Use MedicalCenter instead.');
    }
}
