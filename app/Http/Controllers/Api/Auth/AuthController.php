<?php

namespace App\Http\Controllers\Api\Auth;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Support\Str;
use App\Models\LoginSession;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
            'mobile.regex'    => 'شماره موبایل باید فرمت معتبر داشته باشد (مثلاً 09181234567).',
        ]);

        $mobile          = preg_replace('/^(\+98|98|0)/', '', $request->mobile);
        $formattedMobile = '0' . $mobile;

        $user          = User::where('mobile', $formattedMobile)->first();
        $loginAttempts = new LoginAttemptsService();

        // اگر کاربر وجود ندارد یا وضعیتش صفر است، کاربر جدید ثبت‌نام می‌شود
        if (! $user || $user->status === 0) {
            if (! $user) {
                // ثبت‌نام کاربر جدید
                $user = User::create([
                    'mobile' => $formattedMobile,
                    'status' => 1, // کاربر به صورت پیش‌فرض فعال می‌شود
                ]);
            } else {
                // اگر کاربر وجود دارد ولی غیرفعال است، وضعیتش به فعال تغییر کند
                $user->update(['status' => 1]);
            }
        }

        if ($loginAttempts->isLocked($formattedMobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($formattedMobile);
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

        $loginAttempts->incrementLoginAttempt($user->id, $formattedMobile, '', '', '');
        $otpCode = rand(1000, 9999);
        $token   = Str::random(60);

        Otp::create([
            'token'    => $token,
            'user_id'  => $user->id,
            'otp_code' => $otpCode,
            'login_id' => $user->mobile,
            'type'     => 0,
        ]);

        LoginSession::create([
            'token'      => $token,
            'user_id'    => $user->id,
            'step'       => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $messagesService = new MessageService(
            SmsService::create(100253, $user->mobile, [$otpCode])
        );
        $messagesService->send();

        return response()->json([
            'status'  => 'success',
            'message' => 'کد تایید ارسال شد',
            'data'    => [
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
        $mobile        = $otp?->user?->mobile ?? $otp?->login_id ?? 'unknown';

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

        if (! $otp || $otp->otp_code !== $request->otpCode) {
            $userId = $otp->user_id ?? null;
            $loginAttempts->incrementLoginAttempt($userId, $mobile, '', '', '');
            return response()->json(['status' => 'error', 'message' => 'کد تأیید وارد شده صحیح نیست.', 'data' => null], 422);
        }

        $otp->update(['used' => 1]);
        $user = $otp->user;
        $user->update(['mobile_verified_at' => Carbon::now()]);
        $jwtToken = Auth::guard('api')->login($user);
        $loginAttempts->resetLoginAttempts($user->mobile);
        LoginSession::where('token', $token)->delete();
        LoginLog::create(['user_id' => $user->id, 'user_type' => 'user', 'login_at' => now(), 'ip_address' => $request->ip(), 'device' => $request->header('User-Agent')]);

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
        $mobile        = $otp->user?->mobile ?? $otp->login_id ?? 'unknown';

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
            'user_id'  => $otp->user_id,
            'otp_code' => $otpCode,
            'login_id' => $otp->user->mobile,
            'type'     => 0,
        ]);

        LoginSession::where('token', $token)->delete();
        LoginSession::create([
            'token'      => $newToken,
            'user_id'    => $otp->user_id,
            'step'       => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $messagesService = new MessageService(
            SmsService::create(100253, $otp->user->mobile, [$otpCode])
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
            // دریافت توکن از درخواست
            $token = JWTAuth::getToken();

            if ($token) {
                // ابطال توکن در سرور
                JWTAuth::invalidate($token);
            }

            // دریافت کاربر لاگین شده
            $user = Auth::guard('api')->user();

            if ($user) {
                $logoutTime = now();
                LoginLog::where('user_id', $user->id)
                    ->whereNull('logout_at')
                    ->latest()
                    ->first()?->update(['logout_at' => $logoutTime]);

                // حذف کوکی توکن
                return response()->json([
                    'status'  => 'success',
                    'message' => 'با موفقیت خارج شدید',
                ])->withCookie(cookie()->forget('auth_token'));
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'با موفقیت خارج شدید',
            ], 200);
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
        $user = Auth::guard('api')->user();
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
        try {
            // توکن رو از کوکی یا هدر بگیر (مثل میدلور)
            $token = $request->cookie('auth_token') ?? $request->bearerToken();

            if (! $token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن ارائه نشده است',
                    'data'    => null,
                ], 401);
            }

            // اعتبارسنجی توکن
            $user = JWTAuth::setToken($token)->authenticate();

            return response()->json([
                'status'  => 'success',
                'message' => 'توکن معتبر است',
                'data'    => [
                    'user' => $user,
                ],
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'data'    => null,
            ], 401);
        }
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
        Log::info('UpdateProfile - Headers: ' . json_encode($request->headers->all()));
        Log::info('UpdateProfile - Cookies: ' . json_encode($request->cookies->all()));

        // گرفتن توکن از کوکی یا هدر
        $token = $request->cookie('auth_token') ?: $request->bearerToken();
        Log::info('UpdateProfile - Token retrieved: ' . ($token ?: 'None'));

        if (! $token) {
            Log::warning('UpdateProfile - No token provided');
            return response()->json([
                'status'  => 'error',
                'message' => 'توکن یافت نشد',
                'data'    => null,
            ], 401);
        }

        try {
            // دیکد کردن توکن برای دیباگ
            $payload = JWTAuth::setToken($token)->getPayload();
            Log::info('UpdateProfile - Token payload: ' . json_encode($payload->toArray()));

            // اعتبارسنجی توکن و گرفتن کاربر
            $user = JWTAuth::setToken($token)->authenticate();
            if (! $user) {
                Log::warning('UpdateProfile - User not found for token: ' . $token);
                return response()->json([
                    'status'  => 'error',
                    'message' => 'کاربر یافت نشد',
                    'data'    => null,
                ], 401);
            }

            Log::info('UpdateProfile - User authenticated: ' . $user->id);

            // اعتبارسنجی درخواست
            $request->validate([
                'first_name'    => 'nullable|string|max:255',
                'last_name'     => 'nullable|string|max:255',
                'national_code' => 'nullable|string|size:10|unique:users,national_code,' . $user->id,
                'date_of_birth' => 'nullable|date|before:today',
                'sex'           => 'nullable|in:male,female',
                'zone_city_id'  => 'nullable|exists:zone,id,level,2',
                'zone_province_id'  => 'nullable|exists:zone,id,level,1',
                'email'         => 'nullable|email|unique:users,email,' . $user->id,
                'address'       => 'nullable|string|max:1000',
            ]);

            // به‌روزرسانی اطلاعات کاربر
            $user->update([
                'first_name'    => $request->input('first_name', $user->first_name),
                'last_name'     => $request->input('last_name', $user->last_name),
                'national_code' => $request->input('national_code', $user->national_code),
                'date_of_birth' => $request->input('date_of_birth', $user->date_of_birth),
                'sex'           => $request->input('sex', $user->sex),
                'zone_city_id'  => $request->input('zone_city_id', $user->zone_city_id),
                'zone_province_id'  => $request->input('zone_province_id', $user->zone_province_id),
                'email'         => $request->input('email', $user->email),
                'address'       => $request->input('address', $user->address),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'اطلاعات با موفقیت به‌روزرسانی شد',
                'data'    => ['user' => $user->fresh()],
            ], 200);

        } catch (TokenExpiredException $e) {
            Log::error('UpdateProfile - Token expired: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'توکن منقضی شده است. لطفاً دوباره وارد شوید.',
                'data'    => null,
            ], 401);
        } catch (TokenInvalidException $e) {
            Log::error('UpdateProfile - Token invalid: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'توکن نامعتبر است.',
                'data'    => null,
            ], 401);
        } catch (JWTException $e) {
            Log::error('UpdateProfile - JWT error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطا در پردازش توکن: ' . $e->getMessage(),
                'data'    => null,
            ], 401);
        }
    }
}
