<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;
use App\Models\LoginLog;
use App\Models\LoginSession;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

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

        if (! $user) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null, null);
            return response()->json([
                'status'  => 'error',
                'message' => 'کاربری با این شماره تلفن وجود ندارد.',
                'data'    => null,
            ], 422);
        }

        if ($user->status !== 1) {
            $loginAttempts->incrementLoginAttempt($user->id, $formattedMobile, '', '', '');
            return response()->json([
                'status'  => 'error',
                'message' => 'حساب کاربری شما فعال نیست.',
                'data'    => null,
            ], 422);
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
            'message' => 'کد OTP ارسال شد',
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
            return response()->json([
                'status'  => 'error',
                'message' => 'کد تأیید وارد شده صحیح نیست.',
                'data'    => null,
            ], 422);
        }

        $otp->update(['used' => 1]);
        $user = $otp->user;

        if (empty($user->mobile_verified_at)) {
            $user->update(['mobile_verified_at' => Carbon::now()]);
        }

        Auth::guard('user')->login($user);
        $loginAttempts->resetLoginAttempts($user->mobile);
        LoginSession::where('token', $token)->delete();

        LoginLog::create([
            'user_id'    => $user->id,
            'user_type'  => 'user',
            'login_at'   => now(),
            'ip_address' => $request->ip(),
            'device'     => $request->header('User-Agent'),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'ورود با موفقیت انجام شد',
            'data'    => [
                'user' => [
                    'id'                 => $user->id,
                    'mobile'             => $user->mobile,
                    'mobile_verified_at' => $user->mobile_verified_at,
                ],
            ],
        ], 200);
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
        $user = Auth::guard('user')->user();
        if ($user) {
            $logoutTime = now();
            LoginLog::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->latest()
                ->first()?->update(['logout_at' => $logoutTime]);
            Auth::guard('user')->logout();

            return response()->json([
                'status'  => 'success',
                'message' => 'شما با موفقیت خارج شدید',
                'data'    => [
                    'logout_at' => $logoutTime->toIso8601String(),
                ],
            ], 200);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'شما با موفقیت خارج شدید',
            'data'    => null,
        ], 200);
    }
}
