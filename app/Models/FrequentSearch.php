<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrequentSearch extends Model
{
    protected $fillable = [
        'specialty_id',
        'search_text',
        'search_count',
    ];

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}
