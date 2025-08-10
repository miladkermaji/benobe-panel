<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'story_id',
        'liker_type',
        'liker_id',
    ];

    /**
     * روابط مدل
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * رابطه polymorphic با liker (هر نوع کاربری)
     */
    public function liker()
    {
        return $this->morphTo();
    }

    /**
     * بررسی اینکه آیا liker استوری را لایک کرده است
     */
    public static function hasLikerLiked($liker, $storyId)
    {
        if (!$liker) {
            return false;
        }

        return static::where('liker_type', get_class($liker))
                    ->where('liker_id', $liker->id)
                    ->where('story_id', $storyId)
                    ->exists();
    }

    /**
     * لایک کردن استوری
     */
    public static function likeStory($liker, $storyId)
    {
        $story = Story::find($storyId);

        if (!$story || !$liker) {
            return false;
        }

        // بررسی اینکه آیا قبلاً لایک شده است
        $existingLike = static::where('liker_type', get_class($liker))
                              ->where('liker_id', $liker->id)
                              ->where('story_id', $storyId)
                              ->first();

        if ($existingLike) {
            return false; // قبلاً لایک شده است
        }

        // ایجاد لایک جدید
        $like = static::create([
            'liker_type' => get_class($liker),
            'liker_id' => $liker->id,
            'story_id' => $storyId,
        ]);

        // افزایش تعداد لایک در استوری
        $story->incrementLikes();

        return $like;
    }

    /**
     * حذف لایک استوری
     */
    public static function unlikeStory($liker, $storyId)
    {
        $story = Story::find($storyId);

        if (!$story || !$liker) {
            return false;
        }

        // پیدا کردن لایک موجود
        $like = static::where('liker_type', get_class($liker))
                      ->where('liker_id', $liker->id)
                      ->where('story_id', $storyId)
                      ->first();

        if (!$like) {
            return false; // لایک وجود ندارد
        }

        // حذف لایک
        $like->delete();

        // کاهش تعداد لایک در استوری
        $story->decrementLikes();

        return true;
    }

    /**
     * تغییر وضعیت لایک (لایک/آنلایک)
     */
    public static function toggleLike($liker, $storyId)
    {
        if (static::hasLikerLiked($liker, $storyId)) {
            return static::unlikeStory($liker, $storyId);
        } else {
            return static::likeStory($liker, $storyId);
        }
    }

    /**
     * دریافت لیست liker های یک استوری
     */
    public static function getStoryLikers($storyId, $limit = 50)
    {
        return static::where('story_id', $storyId)
                    ->with('liker')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->pluck('liker')
                    ->unique('id');
    }

    /**
     * دریافت آمار لایک بر اساس نوع liker
     */
    public static function getLikesByLikerType($storyId, $likerType = null)
    {
        $query = static::where('story_id', $storyId);

        if ($likerType) {
            $query->where('liker_type', $likerType);
        }

        return $query->selectRaw('liker_type, COUNT(*) as count')
                    ->groupBy('liker_type')
                    ->get();
    }

    /**
     * دریافت آخرین لایک‌های یک liker
     */
    public static function getLikerRecentLikes($liker, $limit = 10)
    {
        if (!$liker) {
            return collect();
        }

        return static::where('liker_type', get_class($liker))
                    ->where('liker_id', $liker->id)
                    ->with('story')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }
}
