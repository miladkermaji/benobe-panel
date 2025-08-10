<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryView extends Model
{
    use HasFactory;

    protected $fillable = [
        'story_id',
        'viewer_type',
        'viewer_id',
        'ip_address',
        'user_agent',
        'session_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * روابط مدل
     */
    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * رابطه polymorphic با viewer (هر نوع کاربری)
     */
    public function viewer()
    {
        return $this->morphTo();
    }

    /**
     * ثبت بازدید جدید
     */
    public static function recordView($storyId, $viewer = null, $request = null)
    {
        $story = Story::find($storyId);

        if (!$story) {
            return false;
        }

        $data = [
            'story_id' => $storyId,
            'viewed_at' => now(),
        ];

        // اگر viewer موجود باشد، اطلاعات polymorphic را اضافه کن
        if ($viewer) {
            $data['viewer_type'] = get_class($viewer);
            $data['viewer_id'] = $viewer->id;
        }

        // اگر درخواست HTTP موجود باشد، اطلاعات اضافی را اضافه کن
        if ($request) {
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            $data['session_id'] = $request->session()->getId();
        }

        // بررسی اینکه آیا این بازدید قبلاً ثبت شده است
        $existingView = static::where('story_id', $storyId)
                              ->where('ip_address', $data['ip_address'] ?? null)
                              ->where('session_id', $data['session_id'] ?? null)
                              ->where('viewed_at', '>=', now()->subMinutes(5)) // در 5 دقیقه گذشته
                              ->first();

        if ($existingView) {
            return $existingView; // بازدید تکراری
        }

        // ایجاد بازدید جدید
        $view = static::create($data);

        // افزایش تعداد بازدید در استوری
        $story->incrementViews();

        return $view;
    }

    /**
     * دریافت تعداد بازدیدهای یک استوری
     */
    public static function getViewsCount($storyId)
    {
        return static::where('story_id', $storyId)->count();
    }

    /**
     * دریافت بازدیدهای یک استوری در بازه زمانی مشخص
     */
    public static function getViewsInPeriod($storyId, $startDate, $endDate)
    {
        return static::where('story_id', $storyId)
                    ->whereBetween('viewed_at', [$startDate, $endDate])
                    ->get();
    }

    /**
     * دریافت آمار بازدیدهای روزانه
     */
    public static function getDailyViewsStats($storyId, $days = 7)
    {
        return static::where('story_id', $storyId)
                    ->where('viewed_at', '>=', now()->subDays($days))
                    ->selectRaw('DATE(viewed_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
    }

    /**
     * دریافت آمار بازدیدهای ساعتی
     */
    public static function getHourlyViewsStats($storyId, $hours = 24)
    {
        return static::where('story_id', $storyId)
                    ->where('viewed_at', '>=', now()->subHours($hours))
                    ->selectRaw('HOUR(viewed_at) as hour, COUNT(*) as count')
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get();
    }

    /**
     * بررسی اینکه آیا viewer استوری را دیده است
     */
    public static function hasViewerViewed($viewer, $storyId)
    {
        if (!$viewer) {
            return false;
        }

        return static::where('viewer_type', get_class($viewer))
                    ->where('viewer_id', $viewer->id)
                    ->where('story_id', $storyId)
                    ->exists();
    }

    /**
     * دریافت آخرین بازدیدهای یک viewer
     */
    public static function getViewerRecentViews($viewer, $limit = 10)
    {
        if (!$viewer) {
            return collect();
        }

        return static::where('viewer_type', get_class($viewer))
                    ->where('viewer_id', $viewer->id)
                    ->with('story')
                    ->orderBy('viewed_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * دریافت آمار بازدید بر اساس نوع viewer
     */
    public static function getViewsByViewerType($storyId, $viewerType = null)
    {
        $query = static::where('story_id', $storyId);

        if ($viewerType) {
            $query->where('viewer_type', $viewerType);
        }

        return $query->selectRaw('viewer_type, COUNT(*) as count')
                    ->groupBy('viewer_type')
                    ->get();
    }

    /**
     * دریافت لیست viewer های یک استوری
     */
    public static function getStoryViewers($storyId, $limit = 50)
    {
        return static::where('story_id', $storyId)
                    ->with('viewer')
                    ->orderBy('viewed_at', 'desc')
                    ->limit($limit)
                    ->get()
                    ->pluck('viewer')
                    ->unique('id');
    }
}
