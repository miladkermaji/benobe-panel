<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $table = 'contact_messages';

    protected $fillable = [
        'full_name', 'email', 'country_code', 'phone', 'subject',
        'message', 'status', 'admin_reply', 'replied_at'
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getStatusDisplayNameAttribute()
    {
        return match($this->status) {
            'new' => 'جدید',
            'read' => 'خوانده شده',
            'replied' => 'پاسخ داده شده',
            'closed' => 'بسته شده',
            default => 'نامشخص'
        };
    }

    public function getFullPhoneAttribute()
    {
        return $this->country_code . $this->phone;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'new' => 'bg-danger',
            'read' => 'bg-warning',
            'replied' => 'bg-success',
            'closed' => 'bg-secondary',
            default => 'bg-secondary'
        };
    }
}
 