<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalCenter;
use App\Models\MedicalCenterReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MedicalCenterProfileController extends Controller
{
    /**
     * دریافت پروفایل مرکز درمانی
     *
     * @param int $centerId
     * @return JsonResponse
     */
    public function show($centerId): JsonResponse
    {
        try {
            // ابتدا مرکز درمانی را بدون eager loading دریافت می‌کنیم
            $medicalCenter = MedicalCenter::findOrFail($centerId);

            // بررسی نوع مرکز درمانی - اگر policlinic باشد، خطا برگردان
            if ($medicalCenter->type === 'policlinic') {
                return response()->json([
                    'success' => false,
                    'message' => 'این نوع مرکز درمانی در دسترس نیست',
                    'error' => 'policlinic_not_allowed'
                ], 404);
            }

            // محاسبه آمار
            $totalAppointments = $medicalCenter->appointments()->where('status', 'completed')->count();
            $totalSuccessfulAppointments = $medicalCenter->appointments()->where('status', 'completed')->count();

            // دریافت روابط به صورت جداگانه
            $province = $medicalCenter->province;
            $city = $medicalCenter->city;
            $doctors = $medicalCenter->doctors()->with('specialty')->get();
            $specialties = $medicalCenter->specialties();
            $insurances = $medicalCenter->insurances();
            $services = $medicalCenter->services();
            $recentReviews = $medicalCenter->approvedReviews()->latest()->limit(5)->get();

            // ساختار پاسخ
            $response = [
                'success' => true,
                'data' => [
                    'center_details' => [
                        'id' => $medicalCenter->id,
                        'name' => $medicalCenter->name,
                        'title' => $medicalCenter->title,
                        'city' => $city?->name,
                        'province' => $province?->name,
                        'type' => $medicalCenter->type,
                        'is_24_7' => $medicalCenter->type === 'hospital' || $medicalCenter->type === 'treatment_centers',
                        'rating' => [
                            'value' => $medicalCenter->average_rating,
                            'reviews_count' => $medicalCenter->reviews_count
                        ],
                        'recommendation_percentage' => $medicalCenter->recommendation_percentage,
                        'total_successful_appointments' => $totalSuccessfulAppointments,
                        'image_url' => $medicalCenter->avatar ? asset('storage/' . $medicalCenter->avatar) : null,
                        'description' => $medicalCenter->description,
                        'working_hours' => [
                            'start_time' => $medicalCenter->start_time,
                            'end_time' => $medicalCenter->end_time,
                            'working_days' => $medicalCenter->working_days
                        ],
                        'consultation_fee' => $medicalCenter->consultation_fee,
                        'prescription_tariff' => $medicalCenter->prescription_tariff,
                        'payment_methods' => $medicalCenter->payment_methods,
                        'center_tariff_type' => $medicalCenter->Center_tariff_type,
                        'daycare_centers' => $medicalCenter->Daycare_centers,
                    ],
                    'address' => [
                        'full_address' => $medicalCenter->address,
                        'phone_number' => $medicalCenter->phone_number,
                        'secretary_phone' => $medicalCenter->secretary_phone,
                        'postal_code' => $medicalCenter->postal_code,
                        'latitude' => $medicalCenter->latitude,
                        'longitude' => $medicalCenter->longitude,
                        'location_confirmed' => $medicalCenter->location_confirmed
                    ],
                    'doctors' => $doctors->map(function ($doctor) {
                        return [
                            'id' => $doctor->id,
                            'name' => $doctor->full_name,
                            'specialty' => $doctor->specialty?->name,
                            'rating' => [
                                'value' => $doctor->average_rating,
                                'reviews_count' => $doctor->reviews()->count()
                            ],
                            'successful_appointments' => $doctor->appointments()->where('status', 'completed')->count(),
                            'first_available_appointment' => $doctor->appointments()
                                ->where('status', 'pending')
                                ->where('appointment_date', '>=', now())
                                ->orderBy('appointment_date')
                                ->first()?->appointment_date
                        ];
                    }),
                    'specialties' => $specialties->map(function ($specialty) {
                        return [
                            'id' => $specialty->id,
                            'name' => $specialty->name
                        ];
                    }),
                    'insurances' => $insurances->map(function ($insurance) {
                        return [
                            'id' => $insurance->id,
                            'name' => $insurance->name,
                            'image_url' => $insurance->image_path ? asset('storage/' . $insurance->image_path) : null
                        ];
                    }),
                    'services' => $services->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'name' => $service->name,
                            'description' => $service->description
                        ];
                    }),
                    'gallery' => collect($medicalCenter->galleries ?? [])->map(function ($image) {
                        return [
                            'url' => asset('storage/' . $image),
                            'alt_text' => 'تصویر مرکز درمانی'
                        ];
                    }),
                    'recent_reviews' => $recentReviews->map(function ($review) {
                        return [
                            'id' => $review->id,
                            'user_name' => $review->user_name,
                            'comment' => $review->comment,
                            'rating' => $review->overall_score,
                            'recommendation' => $review->recommend_center ? 'suggest' : 'not_suggest',
                            'waiting_time' => $review->waiting_time,
                            'created_at' => $review->created_at->format('Y-m-d H:i:s')
                        ];
                    }),
                    'additional_info' => [
                        'siam_code' => $medicalCenter->siam_code,
                        'is_main_center' => $medicalCenter->is_main_center,
                        'phone_numbers' => $medicalCenter->phone_numbers,
                        'documents' => $medicalCenter->documents,
                        'slug' => $medicalCenter->slug
                    ]
                ]
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت اطلاعات مرکز درمانی',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * دریافت نظرات مرکز درمانی
     *
     * @param Request $request
     * @param int $centerId
     * @return JsonResponse
     */
    public function reviews(Request $request, $centerId): JsonResponse
    {
        try {
            // اعتبارسنجی پارامترها
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'limit' => 'integer|min:1|max:50',
                'sort' => 'in:latest,oldest,rating_high,rating_low',
                'filter_rating' => 'integer|min:1|max:5',
                'filter_recommendation' => 'in:suggest,not_suggest,all'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'پارامترهای نامعتبر',
                    'errors' => $validator->errors()
                ], 400);
            }

            // بررسی وجود مرکز درمانی
            $medicalCenter = MedicalCenter::findOrFail($centerId);

            // بررسی نوع مرکز درمانی - اگر policlinic باشد، خطا برگردان
            if ($medicalCenter->type === 'policlinic') {
                return response()->json([
                    'success' => false,
                    'message' => 'این نوع مرکز درمانی در دسترس نیست',
                    'error' => 'policlinic_not_allowed'
                ], 404);
            }

            // تنظیم پارامترهای پیش‌فرض
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $sort = $request->get('sort', 'latest');
            $filterRating = $request->get('filter_rating');
            $filterRecommendation = $request->get('filter_recommendation', 'all');

            // شروع کوئری
            $query = MedicalCenterReview::with(['userable', 'appointment'])
                ->where('medical_center_id', $centerId)
                ->where('status', true); // فقط نظرات تأیید شده

            // فیلتر بر اساس امتیاز
            if ($filterRating) {
                $query->where('overall_score', $filterRating);
            }

            // فیلتر بر اساس پیشنهاد
            if ($filterRecommendation !== 'all') {
                $recommend = $filterRecommendation === 'suggest';
                $query->where('recommend_center', $recommend);
            }

            // مرتب‌سازی
            switch ($sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'rating_high':
                    $query->orderBy('overall_score', 'desc');
                    break;
                case 'rating_low':
                    $query->orderBy('overall_score', 'asc');
                    break;
                default: // latest
                    $query->orderBy('created_at', 'desc');
            }

            // دریافت نتایج با pagination
            $reviews = $query->paginate($limit, ['*'], 'page', $page);

            // ساختار پاسخ
            $response = [
                'success' => true,
                'data' => [
                    'reviews' => $reviews->items(),
                    'pagination' => [
                        'current_page' => $reviews->currentPage(),
                        'last_page' => $reviews->lastPage(),
                        'per_page' => $reviews->perPage(),
                        'total' => $reviews->total(),
                        'from' => $reviews->firstItem(),
                        'to' => $reviews->lastItem()
                    ]
                ]
            ];

            return response()->json($response, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'مرکز درمانی یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت نظرات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
