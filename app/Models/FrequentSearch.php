<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrequentSearch extends Model
{
    protected $fillable = [
        'specialty_id',
        'search_text',
        'search_count',
        'user_id',
    ];

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
