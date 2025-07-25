<?php

namespace App\Http\Controllers\Api;

use App\Models\SubUser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
                $user = Auth::user() ?? Auth::guard('doctor')->user() ?? Auth::guard('manager')->user() ?? Auth::guard('secretary')->user();
                if (! $user) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کاربر یافت نشد',
                        'data'    => null,
                    ], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن کاربران زیرمجموعه با اطلاعات کامل (مطابق owner)
            $subUsers = SubUser::with(['subuserable'])
                ->where('owner_id', $user->id)
                ->where('owner_type', get_class($user))
                ->where('status', 'active')
                ->get();

            // فرمت کردن داده‌ها
            $formattedSubUsers = $subUsers->map(function ($subUser) use ($user) {
                $subuserable = $subUser->subuserable;
                $subuserableData = null;
                if ($subuserable) {
                    if ($subUser->subuserable_type === 'App\\Models\\User') {
                        $subuserableData = [
                            'id'                 => $subuserable->id,
                            'mobile'             => $subuserable->mobile,
                            'first_name'         => $subuserable->first_name ?? null,
                            'last_name'          => $subuserable->last_name ?? null,
                            'profile_photo_path' => $subuserable->profile_photo_path ?? null,
                        ];
                    } elseif ($subUser->subuserable_type === 'App\\Models\\Doctor') {
                        $subuserableData = [
                            'id'                 => $subuserable->id,
                            'first_name'         => $subuserable->first_name,
                            'last_name'          => $subuserable->last_name,
                            'license_number'     => $subuserable->license_number,
                            'profile_photo_path' => $subuserable->profile_photo_path,
                        ];
                    } // add more types if needed
                }
                return [
                    'id'         => $subUser->id,
                    'status'     => $subUser->status,
                    'created_at' => $subUser->created_at ? $subUser->created_at->toIso8601String() : null,
                    'updated_at' => $subUser->updated_at ? $subUser->updated_at->toIso8601String() : null,
                    'subuserable' => $subuserableData,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $formattedSubUsers,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
    public function addSubUser(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'national_code' => 'required|string|size:10|regex:/^[0-9]{10}$/',
            'mobile' => 'required|string|regex:/^09[0-9]{9}$/',
            'birth_date' => 'nullable|date',
        ], [
            'first_name.required' => 'نام الزامی است.',
            'last_name.required' => 'نام خانوادگی الزامی است.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.size' => 'کد ملی باید 10 رقم باشد.',
            'national_code.regex' => 'فرمت کد ملی معتبر نیست.',
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.regex' => 'فرمت شماره موبایل معتبر نیست.',
            'birth_date.date' => 'تاریخ تولد معتبر نیست.',
        ]);

        // گرفتن owner
        $owner = Auth::user() ?? Auth::guard('doctor')->user() ?? Auth::guard('manager')->user() ?? Auth::guard('secretary')->user();
        if (!$owner) {
            return response()->json([
                'status' => 'error',
                'message' => 'کاربر احراز هویت نشده است.',
                'data' => null,
            ], 401);
        }
        $ownerType = get_class($owner);
        $ownerId = $owner->id;

        // جستجو در همه مدل‌ها فقط بر اساس کد ملی
        $models = [
            ['model' => \App\Models\User::class, 'type' => 'user'],
            ['model' => \App\Models\Doctor::class, 'type' => 'doctor'],
            ['model' => \App\Models\Secretary::class, 'type' => 'secretary'],
            ['model' => \App\Models\Admin\Manager::class, 'type' => 'manager'],
        ];
        $found = null;
        $foundType = null;
        foreach ($models as $item) {
            $found = $item['model']::where('national_code', $validated['national_code'])->first();
            if ($found) {
                $foundType = $item['model'];
                break;
            }
        }

        if ($found) {
            // ثبت در sub_users
            $alreadySubUser = \App\Models\SubUser::where([
                'owner_id' => $ownerId,
                'owner_type' => $ownerType,
                'subuserable_id' => $found->id,
                'subuserable_type' => $foundType,
            ])->exists();
            if ($alreadySubUser) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'کاربری با این کد ملی قبلاً به زیرمجموعه شما اضافه شده است.',
                    'data' => $found,
                    'model_type' => class_basename($foundType),
                ], 200);
            } else {
                \App\Models\SubUser::create([
                    'owner_id' => $ownerId,
                    'owner_type' => $ownerType,
                    'subuserable_id' => $found->id,
                    'subuserable_type' => $foundType,
                    'status' => 'active',
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'کاربری با این کد ملی قبلاً وجود داشت و به زیرمجموعه شما اضافه شد.',
                    'data' => $found,
                    'model_type' => class_basename($foundType),
                ], 200);
            }
        }

        // اگر کد ملی نبود، قبل از ساخت کاربر جدید، شماره موبایل را چک کن
        $mobileUser = \App\Models\User::where('mobile', $validated['mobile'])->first();
        if ($mobileUser) {
            // بررسی وجود در جدول sub_users
            $alreadySubUser = \App\Models\SubUser::where([
                'owner_id' => $ownerId,
                'owner_type' => $ownerType,
                'subuserable_id' => $mobileUser->id,
                'subuserable_type' => \App\Models\User::class,
            ])->exists();
            if ($alreadySubUser) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'کاربری با این شماره موبایل قبلاً به زیرمجموعه شما اضافه شده است.',
                    'data' => $mobileUser,
                    'model_type' => 'User',
                ], 200);
            } else {
                \App\Models\SubUser::create([
                    'owner_id' => $ownerId,
                    'owner_type' => $ownerType,
                    'subuserable_id' => $mobileUser->id,
                    'subuserable_type' => \App\Models\User::class,
                    'status' => 'active',
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'کاربری با این شماره موبایل قبلاً وجود داشت و به زیرمجموعه شما اضافه شد.',
                    'data' => $mobileUser,
                    'model_type' => 'User',
                ], 200);
            }
        }

        // اگر شماره موبایل هم نبود، کاربر جدید بساز
        $user = \App\Models\User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'national_code' => $validated['national_code'],
            'mobile' => $validated['mobile'],
            'birth_date' => $validated['birth_date'] ?? null,
            'status' => 1,
        ]);
        \App\Models\SubUser::create([
            'owner_id' => $ownerId,
            'owner_type' => $ownerType,
            'subuserable_id' => $user->id,
            'subuserable_type' => \App\Models\User::class,
            'status' => 'active',
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'کاربر جدید با موفقیت به زیرمجموعه شما اضافه شد.',
            'data' => $user,
            'model_type' => 'User',
        ], 201);
    }
}
