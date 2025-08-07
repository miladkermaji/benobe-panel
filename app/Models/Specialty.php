<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Cviebrock\EloquentSluggable\Sluggable;

class Specialty extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'specialties';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'status'     => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    // Relationship with Doctors
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'specialty_id');
    }

    // Relationship with Main Specialty (اگر دارید)

    // متد کش‌دار برای گرفتن لیست تخصص‌ها
    public static function getOptimizedList()
    {
        return Cache::remember(
            'specialties_optimized_list',
            now()->addHours(24),
            function () {
                $specialties = self::query()
                    ->select('id', 'name')
                    ->where('status', 1)
                    ->orderBy('name')
                    ->get();

                // اگر خالی بود، مقادیر پیش‌فرض برگردان
                return $specialties->isNotEmpty()
                ? $specialties
                : collect([
                    (object) ['id' => '', 'name' => 'تخصصی انتخاب نشده'],
                ]);
            }
        );
    }

}
