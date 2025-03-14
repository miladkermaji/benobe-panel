<?php
namespace App\Models;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    protected $fillable = [
        'notification_id',
        'recipient_type',
        'recipient_id',
        'phone_number',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function recipient()
    {
        return $this->morphTo();
    }
}
