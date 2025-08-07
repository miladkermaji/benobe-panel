<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerPermission extends Model
{
    protected $fillable = [
        'manager_id',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
