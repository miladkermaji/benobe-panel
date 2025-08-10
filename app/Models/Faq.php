<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $table = 'faqs';

    protected $fillable = [
        'category',
        'question',
        'answer',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active FAQs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered FAQs
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope for citizen FAQs
     */
    public function scopeCitizens($query)
    {
        return $query->where('category', 'citizens');
    }

    /**
     * Scope for doctor FAQs
     */
    public function scopeDoctors($query)
    {
        return $query->where('category', 'doctors');
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayNameAttribute()
    {
        return $this->category === 'citizens' ? 'سؤالات متداول برای شهروندان' : 'سؤالات متداول برای پزشکان';
    }
}
