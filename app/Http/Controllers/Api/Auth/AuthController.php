<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\LoginSession;
use App\Models\LoginLog;
use App\Models\Admin\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
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
        $minutes = floor($seconds / 60);
        $remainingSeconds = round($seconds % 60);
        return "$minutes دقیقه و $remainingSeconds ثانیه";
    }

    /**
     * @bodyParam mobile string required شماره موبایل کاربر (مثال: 09181234567)
     * @response 200 {"message": "کد OTP ارسال شد", "token": "random-token"}
     * @response 422 {"message": "شماره موبایل معتبر نیست"}
     */
    public function loginRegister(Request $request)
    {
        $request->validate([
            'mobile' => [
                'required',
                'string',
                'regex:/^(?!09{1}(\d)\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)\d{7}$/'
            ],
        ], [
            'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل باید فرمت معتبر داشته باشد (مثلاً 09181234567).',
        ]);

        $mobile = preg_replace('/^(\+98|98|0)/', '', $request->mobile);
        $formattedMobile = '0' . $mobile;

        $manager = Manager::where('mobile', $formattedMobile)->first();
        $loginAttempts = new LoginAttemptsService();

        if (!$manager) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null, null);
            return response()->json(['message' => 'کاربری با این شماره تلفن وجود ندارد.'], 422);
        }

        if ($manager->status !== 1) {
            $loginAttempts->incrementLoginAttempt($manager->id, $formattedMobile, '', '', $manager->id);
            return response()->json(['message' => 'حساب کاربری شما فعال نیست.'], 422);
        }

        if ($loginAttempts->isLocked($formattedMobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($formattedMobile);
            $formattedTime = $this->formatTime($remainingTime);
            return response()->json(['message' => "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.", 'remainingTime' => $remainingTime], 429);
        }

        $loginAttempts->incrementLoginAttempt($manager->id, $formattedMobile, '', '', $manager->id);
        $otpCode = rand(1000, 9999);
        $token = Str::random(60);

        Otp::create([
            'token' => $token,
            'manager_id' => $manager->id,
            'otp_code' => $otpCode,
            'login_id' => $manager->mobile,
            'type' => 0,
        ]);

        LoginSession::create([
            'token' => $token,
            'manager_id' => $manager->id,
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $messagesService = new MessageService(
            SmsService::create(100253, $manager->mobile, [$otpCode])
        );
        $messagesService->send();

        return response()->json(['message' => 'کد OTP ارسال شد', 'token' => $token]);
    }

    /**
     * @bodyParam otpCode string required کد OTP وارد شده (مثال: 1234)
     * @response 200 {"message": "ورود با موفقیت انجام شد"}
     * @response 422 {"message": "کد تأیید نامعتبر است"}
     */
    public function loginConfirm(Request $request, $token)
    {
        $request->validate(['otpCode' => 'required|string|size:4']);

        $loginSession = LoginSession::where('token', $token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            return response()->json(['message' => 'توکن منقضی شده یا نامعتبر است.'], 422);
        }

        $otp = Otp::where('token', $token)
            ->where('used', 0)
            ->where('created_at', '>=', Carbon::now()->subMinutes(2))
            ->first();

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp?->manager?->mobile ?? $otp?->login_id ?? 'unknown';

        if ($loginAttempts->isLocked($mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
            $formattedTime = $this->formatTime($remainingTime);
            return response()->json(['message' => "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.", 'remainingTime' => $remainingTime], 429);
        }

        if (!$otp || $otp->otp_code !== $request->otpCode) {
            $userId = $otp->manager_id ?? null;
            $loginAttempts->incrementLoginAttempt($userId, $mobile, '', '', $userId);
            return response()->json(['message' => 'کد تأیید وارد شده صحیح نیست.'], 422);
        }

        $otp->update(['used' => 1]);
        $user = $otp->manager;

        if (empty($user->mobile_verified_at)) {
            $user->update(['mobile_verified_at' => Carbon::now()]);
        }

        Auth::guard('manager')->login($user);
        $loginAttempts->resetLoginAttempts($user->mobile);
        LoginSession::where('token', $token)->delete();

        LoginLog::create([
            'manager_id' => $user->id,
            'user_type' => 'manager',
            'login_at' => now(),
            'ip_address' => $request->ip(),
            'device' => $request->header('User-Agent'),
        ]);

        return response()->json(['message' => 'ورود با موفقیت انجام شد']);
    }

    /**
     * @response 200 {"message": "کد جدید ارسال شد", "token": "new-random-token"}
     * @response 422 {"message": "توکن منقضی شده است"}
     */
    public function resendOtp(Request $request, $token)
    {
        $loginSession = LoginSession::where('token', $token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            return response()->json(['message' => 'توکن منقضی شده است'], 422);
        }

        $otp = Otp::where('token', $token)->first();
        if (!$otp) {
            return response()->json(['message' => 'توکن نامعتبر است'], 422);
        }

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp->manager?->mobile ?? $otp->login_id ?? 'unknown';

        if ($loginAttempts->isLocked($mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
            $formattedTime = $this->formatTime($remainingTime);
            return response()->json(['message' => "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.", 'remainingTime' => $remainingTime], 429);
        }

        $otpCode = rand(1000, 9999);
        $newToken = Str::random(60);

        Otp::create([
            'token' => $newToken,
            'manager_id' => $otp->manager_id,
            'otp_code' => $otpCode,
            'login_id' => $otp->manager->mobile,
            'type' => 0,
        ]);

        LoginSession::where('token', $token)->delete();
        LoginSession::create([
            'token' => $newToken,
            'manager_id' => $otp->manager_id,
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $messagesService = new MessageService(
            SmsService::create(100253, $otp->manager->mobile, [$otpCode])
        );
        $messagesService->send();

        return response()->json(['message' => 'کد جدید ارسال شد', 'token' => $newToken]);
    }

    /**
     * @response 200 {"message": "شما با موفقیت خارج شدید"}
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('manager')->user();
        if ($user) {
            LoginLog::where('manager_id', $user->id)
                ->whereNull('logout_at')
                ->latest()
                ->first()
                ?->update(['logout_at' => now()]);
            Auth::guard('manager')->logout();
        }

        return response()->json(['message' => 'شما با موفقیت خارج شدید']);
    }
}