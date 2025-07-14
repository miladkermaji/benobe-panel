<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insulin extends Model
{
    protected $fillable = [
        'name',
        'status',
        'sort_order',
    ];

    public function prescriptionRequests()
    {
        return $this->belongsToMany(PrescriptionRequest::class, 'prescription_insulin_request')
            ->withPivot('count');
    }
}
