<?php
namespace App\Models;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Model;

class AcademicDegree extends Model
{
    protected $table = 'academic_degrees';

    protected $fillable = [
        'title',
        'category',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'academic_degree_id');
    }

    // Scope برای فعال‌ها
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
