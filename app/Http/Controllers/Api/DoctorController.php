<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDoctorLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class DoctorController extends Controller
{
    /**
     * گرفتن لیست پزشکان لایک‌شده توسط کاربر
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "دکتر محمدی",
     *       "specialty": "متخصص قلب و عروق",
     *       "license_number": "۱۲۳۴۵۶",
     *       "profile_photo_path": "https://example.com/photos/doctor1.jpg",
     *       "liked_at": "2025-03-16T12:00:00Z"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "توکن نامعتبر است",
     *   "data": null
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getMyDoctors(Request $request)
    {
        try {
            // گرفتن توکن از هدر یا کوکی
            $token = $request->bearerToken() ?: $request->cookie('auth_token');
            if (! $token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن ارائه نشده است',
                    'data'    => null,
                ], 401);
            }

            // احراز هویت کاربر
            try {
                $user = JWTAuth::setToken($token)->authenticate();
                if (! $user) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کاربر یافت نشد',
                        'data'    => null,
                    ], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                Log::error('GetMyDoctors - JWT Error: ' . $e->getMessage());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن پزشکان لایک‌شده
            $likedDoctors = UserDoctorLike::where('user_id', $user->id)
                ->with([
                    'doctor' => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'specialty_id', 'license_number', 'profile_photo_path')
                            ->with(['specialty' => function ($query) {
                                $query->select('id', 'name');
                            }]);
                    },
                ])
                ->select('id', 'user_id', 'doctor_id', 'liked_at')
                ->get()
                ->pluck('doctor')
                ->filter();

            // فرمت کردن داده‌ها
            $formattedDoctors = $likedDoctors->map(function ($doctor) {
                $like = UserDoctorLike::where('user_id', auth()->id())
                    ->where('doctor_id', $doctor->id)
                    ->first();

                return [
                    'id'                 => $doctor->id,
                    'name'               => $doctor->first_name . ' ' . $doctor->last_name,
                    'specialty'          => $doctor->specialty ? $doctor->specialty->name : null,
                    'license_number'     => $doctor->license_number,
                    'profile_photo_path' => $doctor->profile_photo_path,
                    'liked_at'           => $like ? $like->liked_at->toIso8601String() : null,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedDoctors,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetMyDoctors - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}
