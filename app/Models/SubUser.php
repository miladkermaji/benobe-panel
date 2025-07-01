<?php

namespace App\Models;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUser extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'subuserable_id', 'subuserable_type', 'status'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function subuserable()
    {
        return $this->morphTo();
    }
}
