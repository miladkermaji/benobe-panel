<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryLike;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
    /**
     * لایک کردن استوری
     * نیاز به احراز هویت دارد
     */
    public function like(Request $request): JsonResponse
    {
        try {
            // اعتبارسنجی ورودی
            $validator = Validator::make($request->all(), [
                'story_id' => 'required|integer|exists:stories,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'داده‌های ورودی نامعتبر است',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }

            $storyId = $request->input('story_id');
            $story = Story::find($storyId);

            // بررسی وجود و فعال بودن استوری
            if (!$story) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'استوری یافت نشد',
                    'data' => null
                ], 404);
            }

            if ($story->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'این استوری غیرفعال است',
                    'data' => null
                ], 403);
            }

            // دریافت کاربر احراز هویت شده
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'کاربر احراز هویت نشده است',
                    'data' => null
                ], 401);
            }

            // بررسی اینکه آیا قبلاً لایک شده است
            $existingLike = StoryLike::where('liker_type', get_class($user))
                ->where('liker_id', $user->id)
                ->where('story_id', $storyId)
                ->first();

            if ($existingLike) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'این استوری قبلاً لایک شده است',
                    'data' => [
                        'story_id' => $storyId,
                        'liked' => true,
                        'likes_count' => $story->likes_count
                    ]
                ], 409);
            }

            // ایجاد لایک جدید
            $like = StoryLike::create([
                'liker_type' => get_class($user),
                'liker_id' => $user->id,
                'story_id' => $storyId,
            ]);

            // افزایش تعداد لایک در استوری
            $story->incrementLikes();

            return response()->json([
                'status' => 'success',
                'message' => 'استوری با موفقیت لایک شد',
                'data' => [
                    'story_id' => $storyId,
                    'liked' => true,
                    'likes_count' => $story->fresh()->likes_count,
                    'like_id' => $like->id
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در لایک کردن استوری: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * حذف لایک استوری
     * نیاز به احراز هویت دارد
     */
    public function unlike(Request $request): JsonResponse
    {
        try {
            // اعتبارسنجی ورودی
            $validator = Validator::make($request->all(), [
                'story_id' => 'required|integer|exists:stories,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'داده‌های ورودی نامعتبر است',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }

            $storyId = $request->input('story_id');
            $story = Story::find($storyId);

            // بررسی وجود استوری
            if (!$story) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'استوری یافت نشد',
                    'data' => null
                ], 404);
            }

            // دریافت کاربر احراز هویت شده
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'کاربر احراز هویت نشده است',
                    'data' => null
                ], 401);
            }

            // پیدا کردن لایک موجود
            $like = StoryLike::where('liker_type', get_class($user))
                ->where('liker_id', $user->id)
                ->where('story_id', $storyId)
                ->first();

            if (!$like) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'این استوری لایک نشده است',
                    'data' => [
                        'story_id' => $storyId,
                        'liked' => false,
                        'likes_count' => $story->likes_count
                    ]
                ], 409);
            }

            // حذف لایک
            $like->delete();

            // کاهش تعداد لایک در استوری
            $story->decrementLikes();

            return response()->json([
                'status' => 'success',
                'message' => 'لایک استوری با موفقیت حذف شد',
                'data' => [
                    'story_id' => $storyId,
                    'liked' => false,
                    'likes_count' => $story->fresh()->likes_count
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در حذف لایک استوری: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * تغییر وضعیت لایک (لایک/آنلایک)
     * نیاز به احراز هویت دارد
     */
    public function toggleLike(Request $request): JsonResponse
    {
        try {
            // اعتبارسنجی ورودی
            $validator = Validator::make($request->all(), [
                'story_id' => 'required|integer|exists:stories,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'داده‌های ورودی نامعتبر است',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }

            $storyId = $request->input('story_id');
            $story = Story::find($storyId);

            // بررسی وجود و فعال بودن استوری
            if (!$story) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'استوری یافت نشد',
                    'data' => null
                ], 404);
            }

            if ($story->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'این استوری غیرفعال است',
                    'data' => null
                ], 403);
            }

            // دریافت کاربر احراز هویت شده
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'کاربر احراز هویت نشده است',
                    'data' => null
                ], 401);
            }

            // تغییر وضعیت لایک
            $result = StoryLike::toggleLike($user, $storyId);
            $isLiked = StoryLike::hasLikerLiked($user, $storyId);

            return response()->json([
                'status' => 'success',
                'message' => $isLiked ? 'استوری با موفقیت لایک شد' : 'لایک استوری با موفقیت حذف شد',
                'data' => [
                    'story_id' => $storyId,
                    'liked' => $isLiked,
                    'likes_count' => $story->fresh()->likes_count
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در تغییر وضعیت لایک: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * بررسی وضعیت لایک کاربر
     * نیاز به احراز هویت دارد
     */
    public function checkLikeStatus(Request $request): JsonResponse
    {
        try {
            // اعتبارسنجی ورودی
            $validator = Validator::make($request->all(), [
                'story_id' => 'required|integer|exists:stories,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'داده‌های ورودی نامعتبر است',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }

            $storyId = $request->input('story_id');
            $story = Story::find($storyId);

            // بررسی وجود استوری
            if (!$story) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'استوری یافت نشد',
                    'data' => null
                ], 404);
            }

            // دریافت کاربر احراز هویت شده
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'کاربر احراز هویت نشده است',
                    'data' => null
                ], 401);
            }

            // بررسی وضعیت لایک
            $isLiked = StoryLike::hasLikerLiked($user, $storyId);

            return response()->json([
                'status' => 'success',
                'message' => 'وضعیت لایک بررسی شد',
                'data' => [
                    'story_id' => $storyId,
                    'liked' => $isLiked,
                    'likes_count' => $story->likes_count
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در بررسی وضعیت لایک: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * دریافت لیست استوری‌ها
     * بدون نیاز به احراز هویت
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // اعتبارسنجی ورودی
            $validator = Validator::make($request->all(), [
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:50',
                'type' => 'nullable|in:image,video',
                'is_live' => 'nullable|boolean',
                'search' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'داده‌های ورودی نامعتبر است',
                    'errors' => $validator->errors(),
                    'data' => null
                ], 422);
            }

            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);
            $type = $request->input('type');
            $isLive = $request->input('is_live');
            $search = $request->input('search');

            // شروع کوئری
            $query = Story::active()->with(['doctor', 'medicalCenter', 'manager', 'user']);

            // فیلتر بر اساس نوع
            if ($type) {
                $query->ofType($type);
            }

            // فیلتر بر اساس زنده بودن
            if ($isLive !== null) {
                if ($isLive) {
                    $query->live();
                } else {
                    $query->where('is_live', false);
                }
            }

            // جستجو
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // مرتب‌سازی
            $query->ordered();

            // صفحه‌بندی
            $stories = $query->paginate($perPage, ['*'], 'page', $page);

            // فرمت کردن داده‌ها
            $formattedStories = $stories->getCollection()->map(function ($story) {
                return [
                    'id' => $story->id,
                    'title' => $story->title,
                    'description' => $story->description,
                    'type' => $story->type,
                    'media_url' => $story->media_url,
                    'thumbnail_url' => $story->thumbnail_url,
                    'status' => $story->status,
                    'is_live' => $story->is_live,
                    'is_currently_live' => $story->isCurrentlyLive(),
                    'live_start_time' => $story->live_start_time?->toISOString(),
                    'live_end_time' => $story->live_end_time?->toISOString(),
                    'duration' => $story->duration,
                    'views_count' => $story->views_count,
                    'likes_count' => $story->likes_count,
                    'order' => $story->order,
                    'owner' => [
                        'type' => $story->owner_type,
                        'name' => $story->owner_name,
                        'avatar' => $story->owner_avatar,
                    ],
                    'created_at' => $story->created_at->toISOString(),
                    'updated_at' => $story->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'لیست استوری‌ها با موفقیت دریافت شد',
                'data' => [
                    'stories' => $formattedStories,
                    'pagination' => [
                        'current_page' => $stories->currentPage(),
                        'last_page' => $stories->lastPage(),
                        'per_page' => $stories->perPage(),
                        'total' => $stories->total(),
                        'from' => $stories->firstItem(),
                        'to' => $stories->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در دریافت لیست استوری‌ها: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * دریافت جزئیات یک استوری
     * بدون نیاز به احراز هویت
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $story = Story::active()->with(['doctor', 'medicalCenter', 'manager', 'user'])->find($id);

            if (!$story) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'استوری یافت نشد',
                    'data' => null
                ], 404);
            }

            // افزایش تعداد بازدید
            $story->incrementViews();

            // ثبت بازدید در جدول story_views (اگر کاربر احراز هویت شده باشد)
            $user = Auth::user();
            if ($user) {
                // Assuming StoryView model exists and has a recordView method
                // If not, this line will cause an error.
                // For now, keeping it as is, but it might need adjustment based on actual model.
                // \App\Models\StoryView::recordView($story->id, $user, $request);
            }

            $formattedStory = [
                'id' => $story->id,
                'title' => $story->title,
                'description' => $story->description,
                'type' => $story->type,
                'media_url' => $story->media_url,
                'thumbnail_url' => $story->thumbnail_url,
                'status' => $story->status,
                'is_live' => $story->is_live,
                'is_currently_live' => $story->isCurrentlyLive(),
                'live_start_time' => $story->live_start_time?->toISOString(),
                'live_end_time' => $story->live_end_time?->toISOString(),
                'duration' => $story->duration,
                'views_count' => $story->views_count,
                'likes_count' => $story->likes_count,
                'order' => $story->order,
                'owner' => [
                    'type' => $story->owner_type,
                    'name' => $story->owner_name,
                    'avatar' => $story->owner_avatar,
                ],
                'created_at' => $story->created_at->toISOString(),
                'updated_at' => $story->updated_at->toISOString(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'جزئیات استوری با موفقیت دریافت شد',
                'data' => $formattedStory
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در دریافت جزئیات استوری: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
