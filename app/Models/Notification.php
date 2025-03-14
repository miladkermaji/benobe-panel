<?php
namespace App\Models;

use App\Models\User;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'target_group',
        'is_active',
        'start_at',
        'end_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_at'  => 'datetime',
        'end_at'    => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    public function isCurrentlyActive()
    {
        $now = now();
        return $this->is_active &&
            (! $this->start_at || $this->start_at <= $now) &&
            (! $this->end_at || $this->end_at >= $now);
    }
}
