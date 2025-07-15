<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionInsurance extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(PrescriptionInsurance::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PrescriptionInsurance::class, 'parent_id');
    }

    public function prescriptions()
    {
        return $this->belongsToMany(\App\Models\PrescriptionRequest::class, 'prescription_request_insurance', 'prescription_insurance_id', 'prescription_request_id')
            ->withPivot('referral_code')
            ->withTimestamps();
    }
}
