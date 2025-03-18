<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @group نظرات
 */
class ReviewController extends Controller
{
    /**
     * گرفتن لیست نظرات
     *
     * این متد لیستی از نظرات تأییدشده را برمی‌گرداند. پارامتر اختیاری `limit` برای محدود کردن تعداد نتایج وجود دارد (اگر مشخص نشود، همه را برمی‌گرداند).
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "message": "نظرات با موفقیت دریافت شدند",
     *   "data": [
     *     {
     *       "id": 1,
     *       "reviewer": {
     *         "id": 1,
     *         "first_name": "علی",
     *         "last_name": "محمدی",
     *         "email": "ali@example.com",
     *         "mobile": "09123456789"
     *       },
     *       "name": null,
     *       "comment": "پزشک بسیار خوبی بود",
     *       "image_url": "http://example.com/storage/reviews/image1.jpg",
     *       "rating": 4,
     *       "created_at": "2025-03-18T13:00:00Z"
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطایی در سرور رخ داده است",
     *   "data": null
     * }
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            $reviews = Review::where('is_approved', 1)
                ->with(['reviewable' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'email', 'mobile');
                }])
                ->select('id', 'reviewable_id', 'reviewable_type', 'name', 'comment', 'image_path', 'rating', 'created_at')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedReviews = $reviews->map(function ($review) {
                $reviewer = $review->reviewable;
                return [
                    'id'         => $review->id,
                    'reviewer'   => $reviewer ? [
                        'id'         => $reviewer->id,
                        'first_name' => $reviewer->first_name,
                        'last_name'  => $reviewer->last_name,
                        'email'      => $reviewer->email,
                        'mobile'     => $reviewer->mobile,
                    ] : null,
                    'name'       => $review->name,
                    'comment'    => $review->comment,
                    'image_url'  => $review->image_url,
                    'rating'     => $review->rating,
                    'created_at' => $review->created_at,
                ];
            })->values();

            return response()->json([
                'status'  => 'success',
                'message' => 'نظرات با موفقیت دریافت شدند',
                'data'    => $formattedReviews,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetReviews - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطایی در سرور رخ داده است',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * ثبت نظر جدید
     *
     * این متد یک نظر جدید ثبت می‌کند. اگر توسط ادمین باشد، می‌تواند بدون وابستگی به مدل ثبت شود.
     *
     * @bodyParam reviewable_id integer|null شناسه مدل مرتبط (اختیاری)
     * @bodyParam reviewable_type string|null نوع مدل مرتبط (اختیاری، مثلاً App\Models\User یا App\Models\Doctor)
     * @bodyParam name string|null نام نویسنده (اختیاری، برای ورود دستی)
     * @bodyParam comment string|null متن نظر (اختیاری)
     * @bodyParam image file|null تصویر نظر (اختیاری)
     * @bodyParam rating integer امتیاز (0 تا 5، الزامی)
     * @response 200 {
     *   "status": "success",
     *   "message": "نظر با موفقیت ثبت شد",
     *   "data": {
     *     "id": 1,
     *     "reviewable_id": null,
     *     "reviewable_type": null,
     *     "name": "کاربر ناشناس",
     *     "comment": "خدمات عالی بود",
     *     "image_url": "http://example.com/storage/reviews/image1.jpg",
     *     "rating": 5,
     *     "is_approved": false,
     *     "created_at": "2025-03-18T13:00:00Z"
     *   }
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "شما برای ثبت نظر به‌عنوان کاربر احراز هویت نشده‌اید" | "شما برای ثبت نظر به‌عنوان پزشک احراز هویت نشده‌اید",
     *   "data": null
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "داده‌های ورودی نادرست است",
     *   "data": null
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "کاربر یافت نشد" | "پزشک یافت نشد",
     *   "data": null
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطایی در سرور رخ داده است",
     *   "data": null
     * }
     */
    public function store(Request $request)
    {
        try {
            // گرفتن کاربر از درخواست (که توسط JwtMiddleware ست شده)
            $user = $request->attributes->get('user');

            $validatedData = $request->validate([
                'reviewable_id'   => 'nullable|integer',
                'reviewable_type' => 'nullable|in:App\\Models\\User,App\\Models\\Doctor',
                'name'            => 'nullable|string|max:255',
                'comment'         => 'nullable|string',
                'image'           => 'nullable|image|max:2048', // حداکثر 2 مگابایت
                'rating'          => 'required|integer|min:0|max:5',
            ]);

            // آپلود تصویر (اگر وجود داشته باشد)
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('reviews', 'public');
            }

            // بررسی وجود مدل مرتبط (اگر مشخص شده باشد)
            if (isset($validatedData['reviewable_id']) && isset($validatedData['reviewable_type'])) {
                $reviewable = $validatedData['reviewable_type']::find($validatedData['reviewable_id']);
                if (! $reviewable) {
                    $message = $validatedData['reviewable_type'] === 'App\\Models\\User' ? 'کاربر یافت نشد' : 'پزشک یافت نشد';
                    return response()->json([
                        'status'  => 'error',
                        'message' => $message,
                        'data'    => null,
                    ], 404);
                }
            }

            $review = Review::create([
                'reviewable_id'   => $validatedData['reviewable_id'] ?? null,
                'reviewable_type' => $validatedData['reviewable_type'] ?? null,
                'name'            => $validatedData['name'] ?? null,
                'comment'         => $validatedData['comment'] ?? null,
                'image_path'      => $imagePath,
                'rating'          => $validatedData['rating'],
                'is_approved'     => $user && $user->user_type == 1, // اگر ادمین باشه، خودکار تأیید بشه
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'نظر با موفقیت ثبت شد',
                'data'    => [
                    'id'              => $review->id,
                    'reviewable_id'   => $review->reviewable_id,
                    'reviewable_type' => $review->reviewable_type,
                    'name'            => $review->name,
                    'comment'         => $review->comment,
                    'image_url'       => $review->image_url,
                    'rating'          => $review->rating,
                    'is_approved'     => $review->is_approved,
                    'created_at'      => $review->created_at,
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'داده‌های ورودی نادرست است',
                'data'    => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('StoreReview - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطایی در سرور رخ داده است',
                'data'    => null,
            ], 500);
        }
    }
}
