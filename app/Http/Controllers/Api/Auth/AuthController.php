<?php

namespace App\Http\Controllers\Api\Auth;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Models\Doctor;
use App\Models\LoginLog;
use App\Models\Secretary;
use Illuminate\Support\Str;
use App\Models\LoginSession;
use Illuminate\Http\Request;
use App\Models\Manager;
use App\Models\MedicalCenter;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\UserTypeDetectionService;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class AuthController extends Controller
{
    private function formatTime($seconds)
    {
        if (is_null($seconds) || $seconds < 0) {
            return '0 دقیقه و 0 ثانیه';
        }
        $minutes          = floor($seconds / 60);
        $remainingSeconds = round($seconds % 60);
        return "$minutes دقیقه و $remainingSeconds ثانیه";
    }

    /**
     * @bodyParam mobile string required شماره موبایل کاربر (مثال: 09181234567)
     * @response 200 {
     *   "status": "success",
     *   "message": "کد OTP ارسال شد",
     *   "data": {
     *     "token": "random-token"
     *   }
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "شماره موبایل معتبر نیست",
     *   "data": null
     * }
     * @response 429 {
     *   "status": "error",
     *   "message": "شما بیش از حد تلاش کرده‌اید. لطفاً 2 دقیقه و 30 ثانیه صبر کنید.",
     *   "data": {
     *     "remaining_time": 150,
     *     "formatted_time": "2 دقیقه و 30 ثانیه"
     *   }
     * }
     */
    public function loginRegister(Request $request)
    {
        $request->validate([
            'mobile' => [
                'required',
                'string',
                'regex:/^(?!09{1}(\d)\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)\d{7}$/',
            ],
        ], [
            'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل باید فرمت معتبر داشته باشد (مثلاً 09181234567).',
        ]);

        $mobile = preg_replace('/^(\+98|98|0)/', '', $request->mobile);
        $formattedMobile = '0' . $mobile;

        $loginAttempts = new LoginAttemptsService();

        if ($loginAttempts->isLocked($formattedMobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($formattedMobile);
            $formattedTime = $this->formatTime($remainingTime);
            return response()->json([
                'status' => 'error',
                'message' => "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.",
                'data' => [
                    'remaining_time' => $remainingTime,
                    'formatted_time' => $formattedTime,
                ],
            ], 429);
        }

        $otpCode = rand(1000, 9999);
        $token = Str::random(60);

        // تعیین نوع کاربر و مدل مربوطه برای OTP
        $userTypeDetection = new UserTypeDetectionService();
        $userInfo = $userTypeDetection->detectUserType($request->mobile);

        // اگر کاربر یافت نشد، در جدول users ثبت شود
        if (!$userInfo['model']) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null, null, null);
            $user = User::create([
                'mobile' => $formattedMobile,
                'status' => 1,
            ]);
            $userInfo = [
                'type' => 'user',
                'model' => $user,
                'model_class' => User::class,
                'model_id' => $user->id,
                'is_active' => true,
            ];
        }

        // ایجاد رکورد OTP با ساختار پولی مورفیک
        Otp::create([
            'token' => $token,
            'otp_code' => $otpCode,
            'login_id' => $formattedMobile,
            'type' => 0, // 0 برای شماره موبایل
            'used' => 0,
            'status' => 0,
            'otpable_type' => $userInfo['model_class'],
            'otpable_id' => $userInfo['model_id'],
        ]);

        LoginSession::create([
            'token' => $token,
            'sessionable_type' => $userInfo['model_class'],
            'sessionable_id' => $userInfo['model_id'],
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $messagesService = new MessageService(
            SmsService::create(100286, $formattedMobile, [$otpCode])
        );
        $messagesService->send();

        return response()->json([
            'status' => 'success',
            'message' => 'کد تایید ارسال شد',
            'data' => [
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * @bodyParam otpCode string required کد OTP وارد شده (مثال: 1234)
     * @response 200 {
     *   "status": "success",
     *   "message": "ورود با موفقیت انجام شد",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "mobile": "09181234567",
     *       "mobile_verified_at": "2025-03-12T10:00:00Z"
     *     }
     *   }
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "کد تأیید نامعتبر است",
     *   "data": null
     * }
     * @response 429 {
     *   "status": "error",
     *   "message": "شما بیش از حد تلاش کرده‌اید. لطفاً 2 دقیقه و 30 ثانیه صبر کنید.",
     *   "data": {
     *     "remaining_time": 150,
     *     "formatted_time": "2 دقیقه و 30 ثانیه"
     *   }
     * }
     */
    public function loginConfirm(Request $request, $token)
    {
        $request->validate(['otpCode' => 'required|string|size:4']);

        $loginSession = LoginSession::where('token', $token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (! $loginSession) {
            return response()->json([
                'status'  => 'error',
                'message' => 'توکن منقضی شده یا نامعتبر است.',
                'data'    => null,
            ], 422);
        }

        $otp = Otp::where('token', $token)
            ->where('used', 0)
            ->where('created_at', '>=', Carbon::now()->subMinutes(2))
            ->first();

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp?->otpable?->mobile ?? $otp?->otpable?->phone_number ?? $otp?->login_id ?? 'unknown';

        if ($loginAttempts->isLocked($mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
            $formattedTime = $this->formatTime($remainingTime);
            return response()->json([
                'status'  => 'error',
                'message' => "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.",
                'data'    => [
                    'remaining_time' => $remainingTime,
                    'formatted_time' => $formattedTime,
                ],
            ], 429);
        }

        if (!$otp || $otp->otp_code !== $request->otpCode) {
            // تعیین نوع کاربر برای incrementLoginAttempt
            $userId = null;
            $doctorId = null;
            $secretaryId = null;
            $managerId = null;
            $medicalCenterId = null;

            if ($otp) {
                switch ($otp->otpable_type) {
                    case User::class:
                        $userId = $otp->otpable_id;
                        break;
                    case Doctor::class:
                        $doctorId = $otp->otpable_id;
                        break;
                    case Secretary::class:
                        $secretaryId = $otp->otpable_id;
                        break;
                    case Manager::class:
                        $managerId = $otp->otpable_id;
                        break;
                    case MedicalCenter::class:
                        $medicalCenterId = $otp->otpable_id;
                        break;
                }
            }

            $loginAttempts->incrementLoginAttempt($userId, $mobile, $doctorId, $secretaryId, $managerId, $medicalCenterId);
            return response()->json(['status' => 'error', 'message' => 'کد تأیید وارد شده صحیح نیست.', 'data' => null], 422);
        }

        $otp->update(['used' => 1]);

        $user = null;
        $guard = null;
        $userType = '';

        // استفاده از رابطه پولی مورفیک
        if ($otp->otpable) {
            $user = $otp->otpable;

            switch ($otp->otpable_type) {
                case Doctor::class:
                    $guard = 'doctor-api';
                    $userType = 'doctor';
                    break;
                case Secretary::class:
                    $guard = 'secretary-api';
                    $userType = 'secretary';
                    break;
                case Manager::class:
                    $guard = 'manager-api';
                    $userType = 'manager';
                    break;
                case User::class:
                    $guard = 'api';
                    $userType = 'user';
                    break;
            }
        }

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'کاربر یافت نشد.', 'data' => null], 404);
        }

        $user->update(['mobile_verified_at' => Carbon::now()]);

        // Add the guard to the token claims to identify user type in middleware
        $customClaims = ['guard' => $guard];
        $jwtToken = JWTAuth::claims($customClaims)->fromUser($user);

        $loginAttempts->resetLoginAttempts($user->mobile);

        LoginSession::where('token', $token)->delete();
        LoginLog::create([
            'loggable_type' => get_class($user),
            'loggable_id' => $user->id,
            'user_type' => $userType,
            'login_at' => now(),
            'ip_address' => $request->ip(),
            'device' => $request->header('User-Agent'),
            'login_method' => 'otp'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'ورود موفقیت‌آمیز بود',
            'data' => [
                'user' => $user,
                'token' => $jwtToken,
            ]
        ])->cookie('auth_token', $jwtToken, 10080, '/', null, false, true, false, 'Strict');
    }

    /**
     * @response 200 {
     *   "status": "success",
     *   "message": "کد جدید ارسال شد",
     *   "data": {
     *     "token": "new-random-token"
     *   }
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "توکن منقضی شده است",
     *   "data": null
     * }
     * @response 429 {
     *   "status": "error",
     *   "message": "شما بیش از حد تلاش کرده‌اید. لطفاً 2 دقیقه و 30 ثانیه صبر کنید.",
     *   "data": {
     *     "remaining_time": 150,
     *     "formatted_time": "2 دقیقه و 30 ثانیه"
     *   }
     * }
     */
    public function resendOtp(Request $request, $token)
    {
        $loginSession = LoginSession::where('token', $token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (! $loginSession) {
            return response()->json([
                'status'  => 'error',
                'message' => 'توکن منقضی شده است',
                'data'    => null,
            ], 422);
        }

        $otp = Otp::where('token', $token)->first();
        if (! $otp) {
            return response()->json([
                'status'  => 'error',
                'message' => 'توکن نامعتبر است',
                'data'    => null,
            ], 422);
        }

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp->otpable?->mobile ?? $otp->otpable?->phone_number ?? $otp->login_id ?? 'unknown';

        if ($loginAttempts->isLocked($mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
            $formattedTime = $this->formatTime($remainingTime);
            return response()->json([
                'status'  => 'error',
                'message' => "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.",
                'data'    => [
                    'remaining_time' => $remainingTime,
                    'formatted_time' => $formattedTime,
                ],
            ], 429);
        }

        $otpCode  = rand(1000, 9999);
        $newToken = Str::random(60);

        Otp::create([
            'token'    => $newToken,
            'otp_code' => $otpCode,
            'login_id' => $otp->otpable->mobile ?? $otp->otpable->phone_number,
            'type'     => 0,
            'otpable_type' => $otp->otpable_type,
            'otpable_id' => $otp->otpable_id,
        ]);

        LoginSession::where('token', $token)->delete();
        LoginSession::create([
            'token'      => $newToken,
            'sessionable_type' => $otp->otpable_type,
            'sessionable_id' => $otp->otpable_id,
            'step'       => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $messagesService = new MessageService(
            SmsService::create(100286, $otp->otpable->mobile ?? $otp->otpable->phone_number, [$otpCode])
        );
        $messagesService->send();

        return response()->json([
            'status'  => 'success',
            'message' => 'کد جدید ارسال شد',
            'data'    => [
                'token' => $newToken,
            ],
        ], 200);
    }

    /**
     * @response 200 {
     *   "status": "success",
     *   "message": "شما با موفقیت خارج شدید",
     *   "data": {
     *     "logout_at": "2025-03-12T10:00:00Z"
     *   }
     * }
     */
    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();

            if ($token) {
                JWTAuth::invalidate($token);
            }

            $user = Auth::user();

            if ($user) {
                $userType = 'unknown';
                if ($user instanceof \App\Models\Doctor) {
                    $userType = 'doctor';
                } elseif ($user instanceof \App\Models\Secretary) {
                    $userType = 'secretary';
                } elseif ($user instanceof \App\Models\Manager) {
                    $userType = 'manager';
                } elseif ($user instanceof \App\Models\User) {
                    $userType = 'user';
                }

                $logoutTime = now();
                LoginLog::where('loggable_type', get_class($user))
                    ->where('loggable_id', $user->id)
                    ->where('user_type', $userType)
                    ->whereNull('logout_at')
                    ->latest()
                    ->first()?->update(['logout_at' => $logoutTime]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'با موفقیت خارج شدید',
            ])->withCookie(cookie()->forget('auth_token'));

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطایی در خروج رخ داد',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'کاربر لاگین نکرده است'], 401);
        }
        return response()->json(['status' => 'success', 'data' => ['user' => $user]]);
    }
    /**
     * @authenticated
     * @header Authorization Bearer {token}
     * @response 200 {
     *   "status": "success",
     *   "message": "توکن معتبر است",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "mobile": "09181234567",
     *       "mobile_verified_at": "2025-03-12T10:00:00Z"
     *     }
     *   }
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "توکن نامعتبر است",
     *   "data": null
     * }
     */
    public function verifyToken(Request $request)
    {
        // The middleware has already validated the token and authenticated the user.
        // We just need to get the user from the Auth facade.
        $user = Auth::user();

        if (!$user) {
            // This case should not be reached if middleware is working correctly.
            return response()->json([
                'status'  => 'error',
                'message' => 'کاربر یافت نشد. لطفاً دوباره وارد شوید.',
                'data'    => null,
            ], 401);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'توکن معتبر است',
            'data'    => [
                'user' => $user,
            ],
        ], 200);
    }
    /**
       * @bodyParam first_name string optional نام کاربر
       * @bodyParam last_name string optional نام خانوادگی کاربر
       * @bodyParam national_code string optional کد ملی (باید یکتا باشد)
       * @bodyParam date_of_birth string optional تاریخ تولد (فرمت: Y-m-d مثلاً 1990-05-15)
       * @bodyParam sex string optional جنسیت (male یا female)
       * @bodyParam zone_city_id integer optional شناسه شهر
       * @bodyParam email string optional ایمیل (باید یکتا باشد)
       * @bodyParam address string optional آدرس
       * @response 200 {
       *   "status": "success",
       *   "message": "اطلاعات با موفقیت به‌روزرسانی شد",
       *   "data": {
       *     "user": {
       *       "id": 1,
       *       "mobile": "09181234567",
       *       "first_name": "علی",
       *       "last_name": "رضایی",
       *       "national_code": "1234567890",
       *       "date_of_birth": "1990-05-15",
       *       "sex": "male",
       *       "zone_city_id": 1,
       *       "email": "ali@example.com",
       *       "address": "تهران، خیابان ولیعصر",
       *       "mobile_verified_at": "2025-03-12T10:00:00Z"
       *     }
       *   }
       * }
       * @response 401 {
       *   "status": "error",
       *   "message": "کاربر احراز هویت نشده است",
       *   "data": null
       * }
       */
    public function updateProfile(Request $request)
    {
        // The `custom-auth.jwt` middleware has already authenticated the user.
        $model = Auth::user();

        if (!$model) {
            Log::warning('UpdateProfile - User not authenticated');
            return response()->json([
                'status' => 'error',
                'message' => 'کاربر احراز هویت نشده است',
                'data' => null,
            ], 401);
        }

        Log::info('UpdateProfile - User authenticated', ['user_id' => $model->id, 'user_class' => get_class($model)]);

        // Define common validation fields
        $commonFields = [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'national_code' => 'nullable|string|size:10',
            'date_of_birth' => 'nullable|date|before:today',
            'sex' => 'nullable|in:male,female,other',
            'zone_city_id' => 'nullable|exists:zone,id,level,2',
            'zone_province_id' => 'nullable|exists:zone,id,level,1',
            'email' => 'nullable|email',
            'address' => 'nullable|string|max:1000',
        ];

        $userForSync = null;

        // Determine validation rules based on user type
        if ($model instanceof Doctor) {
            $validationRules = array_merge($commonFields, [
                'national_code' => 'nullable|string|size:10|unique:doctors,national_code,' . $model->id,
                'email' => 'nullable|email|unique:doctors,email,' . $model->id,
            ]);
            $userForSync = User::where('mobile', $model->mobile)->first();
        } elseif ($model instanceof Secretary) {
            $validationRules = array_merge($commonFields, [
                'sex' => 'nullable|in:male,female', // Secretaries have limited gender options
                'national_code' => 'nullable|string|size:10|unique:secretaries,national_code,' . $model->id . ',id,doctor_id,' . ($model->doctor_id ?? 'NULL') . ',medical_center_id,' . ($model->medical_center_id ?? 'NULL'),
                'email' => 'nullable|email|unique:secretaries,email,' . $model->id,
            ]);
            $userForSync = User::where('mobile', $model->mobile)->first();
        } elseif ($model instanceof Manager) {
            $validationRules = array_merge($commonFields, [
                'national_code' => 'nullable|string|size:10|unique:managers,national_code,' . $model->id,
                'email' => 'nullable|email|unique:managers,email,' . $model->id,
            ]);
            $userForSync = User::where('mobile', $model->mobile)->first();
        } elseif ($model instanceof User) {
            $validationRules = array_merge($commonFields, [
                'sex' => 'nullable|in:male,female', // Users have limited gender options
                'national_code' => 'nullable|string|size:10|unique:users,national_code,' . $model->id,
                'email' => 'nullable|email|unique:users,email,' . $model->id,
            ]);
        } else {
            Log::error('UpdateProfile - Unknown user type', ['user_class' => get_class($model)]);
            return response()->json(['status' => 'error', 'message' => 'نوع کاربر پشتیبانی نمی‌شود.'], 400);
        }

        // Validate the request
        $request->validate($validationRules);

        // Get only the valid fields from the request
        $updateData = $request->only(array_keys($commonFields));
        // جلوگیری از پاک شدن مقدار قبلی شهر و استان در صورت ارسال null یا خالی
        foreach (["zone_city_id", "zone_province_id"] as $field) {
            if (array_key_exists($field, $updateData) && ($updateData[$field] === null || $updateData[$field] === '')) {
                unset($updateData[$field]);
            }
        }
        // تبدیل تاریخ تولد شمسی به میلادی اگر مقدار داشت
        if (isset($updateData['date_of_birth']) && !empty($updateData['date_of_birth'])) {
            try {
                $date = str_replace('/', '-', $updateData['date_of_birth']); // پشتیبانی از هر دو فرمت
                $updateData['date_of_birth'] = \Morilog\Jalali\Jalalian::fromFormat('Y-m-d', $date)->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                // اگر تبدیل نشد، مقدار را تغییر نده
            }
        }
        $updateData = array_filter($updateData, fn ($value) => $value !== null);

        // Update the primary model
        $model->update($updateData);

        // If the user is a doctor/secretary/manager, also sync their data with the main users table
        if ($userForSync) {
            $userUpdateData = $updateData;
            // The 'users' table might not support 'other' for sex
            if (isset($userUpdateData['sex']) && !in_array($userUpdateData['sex'], ['male', 'female'])) {
                unset($userUpdateData['sex']);
            }
            $userForSync->update($userUpdateData);
        }

        // بازگرداندن اطلاعات استان و شهر به صورت relation
        $relations = [];
        if (method_exists($model, 'zoneCity')) {
            $relations[] = 'zoneCity';
        }
        if (method_exists($model, 'zoneProvince')) {
            $relations[] = 'zoneProvince';
        }

        $user = $model->fresh($relations);
        if ($user->date_of_birth) {
            try {
                $user->date_of_birth_jalali = \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($user->date_of_birth))->format('Y/m/d');
            } catch (\Exception $e) {
                $user->date_of_birth_jalali = null;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'اطلاعات با موفقیت به‌روزرسانی شد',
            'data' => ['user' => $user],
        ], 200);
    }
}
