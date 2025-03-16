<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class SubUserController extends Controller
{
    /**
     * گرفتن لیست کاربران زیرمجموعه
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "status": "active",
     *       "created_at": "2025-03-16T12:00:00Z",
     *       "updated_at": "2025-03-16T12:00:00Z",
     *       "doctor": {
     *         "id": 1,
     *         "first_name": "دکتر",
     *         "last_name": "محمدی",
     *         "license_number": "۱۲۳۴۵۶",
     *         "profile_photo_path": "https://example.com/photos/doctor1.jpg"
     *       },
     *       "user": {
     *         "id": 2,
     *         "mobile": "09182718639",
     *         "name": "علی احمدی" // فرض می‌کنیم جدول users فیلد name داره
     *       }
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
    public function getSubUsers(Request $request)
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
                Log::error('GetSubUsers - JWT Error: ' . $e->getMessage());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن کاربران زیرمجموعه با اطلاعات کامل
            $subUsers = SubUser::with([
                'doctor' => function ($query) {
                    $query->select('id','mobile', 'first_name', 'last_name', 'license_number', 'profile_photo_path');
                },
                'user'   => function ($query) {
                    $query->select('id', 'mobile','first_name', 'last_name', 'profile_photo_path'); // اگه فیلد name یا چیز دیگه‌ای دارید، اضافه کنید
                },
            ])
                ->select('id', 'doctor_id', 'user_id', 'status', 'created_at', 'updated_at')
                ->where('status', 'active')
                ->get();

            // فرمت کردن داده‌ها
            $formattedSubUsers = $subUsers->map(function ($subUser) {
                return [
                    'id'         => $subUser->id,
                    'status'     => $subUser->status,
                    'created_at' => $subUser->created_at ? $subUser->created_at->toIso8601String() : null,
                    'updated_at' => $subUser->updated_at ? $subUser->updated_at->toIso8601String() : null,
                    'doctor'     => $subUser->doctor ? [
                        'id'                 => $subUser->doctor->id,
                        'first_name'         => $subUser->doctor->first_name,
                        'last_name'          => $subUser->doctor->last_name,
                        'license_number'     => $subUser->doctor->license_number,
                        'profile_photo_path' => $subUser->doctor->profile_photo_path,
                    ] : null,
                    'user'       => $subUser->user ? [
                        'id'                 => $subUser->user->id,
                        'mobile'             => $subUser->user->mobile,
                        'first_name'         => $subUser->user->first_name,
                        'last_name'          => $subUser->user->last_name,
                        'profile_photo_path' => $subUser->user->profile_photo_path,
                        // اگه فیلد name یا چیز دیگه‌ای توی جدول users دارید، اینجا اضافه کنید
                        // 'name' => $subUser->user->name,
                    ] : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $formattedSubUsers,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetSubUsers - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}
