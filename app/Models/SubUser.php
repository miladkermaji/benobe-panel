<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUser extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'owner_type', 'subuserable_id', 'subuserable_type', 'status'];

    public function owner()
    {
        return $this->morphTo();
    }

    public function subuserable()
    {
        return $this->morphTo();
    }
}
