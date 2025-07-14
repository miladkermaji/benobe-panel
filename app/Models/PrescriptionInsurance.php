<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionInsurance extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'referral_code',
    ];

    public function parent()
    {
        return $this->belongsTo(PrescriptionInsurance::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PrescriptionInsurance::class, 'parent_id');
    }
}
