<?php

namespace App\Http\Controllers\Admin\Auth;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\LoginLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Admin\Manager;
use App\Http\Requests\MobileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\TwoFactorRequest;
use App\Http\Requests\MobilePassRequest;
use App\Http\Requests\OtpDoctorsRequest;
use App\Http\Controllers\Admin\Controller;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Requests\Admin\Authentication\LoginRequest;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class DoctorLoginController extends Controller
{
  public function loginRegisterForm()
  {
    return view('admin.auth.login');
  }

  private function handleRateLimitError($mobile)
  {
    $loginAttempts = new LoginAttemptsService();
    $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
    return response()->json([
      'success' => false,
      'message' => 'شما بیش از حد تلاش کرده‌اید',
      'remaining_time' => $remainingTime
    ], 429);
  }

  public function loginUserPassForm()
  {
    return view('admin.auth.login', ['step' => 3]);
  }

  public function twoFactorForm()
  {
    // بررسی اینکه آیا کاربر از استپ 3 آمده است یا خیر
    if (!session()->has('step3_completed')) {
      return redirect()->route('admin.auth.login-user-pass-form');
    }
    return view('admin.auth.login', ['step' => 4]);
  }

  public function loginConfirmForm($token)
  {
    if (!session()->has('step1_completed')) {
      return redirect()->route('admin.auth.login-register-form');
    }

    $otp = Otp::where('token', $token)->first();

    $remainingTime = $otp
      ? max(0, (int) ($otp->created_at->addMinutes(2)->timestamp - now()->timestamp) * 1000)
      : 0;

    return view('admin.auth.login', [
      'step' => 2,
      'token' => $token,
      'otp' => $otp,
      'remainingTime' => $remainingTime
    ]);
  }


  public function loginRegister(MobileRequest $request)
  {
    $mobile = preg_replace('/^(\+98|98|0)/', '', $request->mobile);
    $formattedMobile = '0' . $mobile;

    $manager = Manager::where('mobile', $formattedMobile)->first();
    $loginAttempts = new LoginAttemptsService();

    if (!$manager) {
      $loginAttempts->incrementLoginAttempt(null, $formattedMobile,null, null, $manager->id);
      return response()->json([
        'success' => false,
        'errors' => ['mobile' => ['کاربری با این شماره تلفن وجود ندارد.']]
      ], 422);
    }

    if ($manager->status !== 1) {
      $loginAttempts->incrementLoginAttempt($manager->id, $formattedMobile, '', '', $manager->id);
      return response()->json([
        'success' => false,
        'errors' => ['mobile' => ['حساب کاربری فعال نیست.']]
      ], 422);
    }

    if ($loginAttempts->isLocked($formattedMobile)) {
      return $this->handleRateLimitError($formattedMobile);
    }

    $loginAttempts->incrementLoginAttempt($manager->id, $formattedMobile, '', '', $manager->id);
    session(['step1_completed' => true]);

    return $this->sendOtp($manager);
  }





  private function sendOtp($user)
  {
    $otpCode = rand(1000, 9999); // تولید کد OTP
    $token = Str::random(60); // تولید توکن

    // ذخیره اطلاعات OTP در دیتابیس
    Otp::create([
      'token' => $token,
      'manager_id' => $user instanceof manager ? $user->id : null,
      'otp_code' => $otpCode,
      'login_id' => $user->mobile,
      'type' => 0,
    ]);

    // ارسال پیامک
    $messagesService = new MessageService(
      SmsService::create(100253, $user->mobile, [$otpCode]) // شناسه الگو به صورت پیش‌فرض 96
    );
    $messagesService->send();
    return response()->json(['token' => $token, 'otp_code' => $otpCode]); // ارسال توکن و کد OTP در پاسخ
  }


  public function loginConfirm(OtpDoctorsRequest $request, $token)
  {
    $otpCode = strrev(implode('', $request->otp));

    $otp = Otp::where('token', $token)
      ->where('used', 0)
      ->where('created_at', '>=', Carbon::now()->subMinutes(2))
      ->first();

    if (!$otp || $otp->otp_code !== $otpCode) {
      $mobile = $otp?->manager?->mobile  ?? $otp?->login_id ?? 'unknown';
      $userId = $otp?->manager_id ?? null;

      (new LoginAttemptsService())->incrementLoginAttempt(
        $userId,
        $mobile ?? 'unknown',
        '',
        '',
        $otp?->manager_id
      );

      return response()->json([
        'success' => false,
        'errors' => ['otp-code' => ['کد وارد شده صحیح نمی‌باشد']]
      ], 422);
    }

    $otp->update(['used' => 1]);
    $user = $otp->manager;

    if (empty($user->mobile_verified_at)) {
      $user->update(['mobile_verified_at' => Carbon::now()]);
    }

    Auth::guard('manager')->login($user);

    (new LoginAttemptsService())->resetLoginAttempts($user->mobile);

    session()->forget('step1_completed');
    LoginLog::create([
      'user_id' => null,
      'manager_id' => $user->id,
      'user_type' => 'manager',
      'login_at' => now(),
      'ip_address' => request()->ip(),
      'device' => request()->header('User-Agent')
    ]);

    return response()->json([
      'success' => true,
      'redirect' => route('admin.index')
    ]);
  }



  public function loginWithMobilePass(MobilePassRequest $request)
  {
    $mobile = $request->mobile;
    $manager = Manager::where('mobile', $mobile)->first();
    $loginAttempts = new LoginAttemptsService();

    if ($loginAttempts->isLocked($mobile)) {
      return $this->handleRateLimitError($mobile);
    }
    if (!$manager || !Hash::check($request->password, $manager->password) || $manager->status !== 1) {
      $loginAttempts->incrementLoginAttempt(
        $manager?->id,
        $mobile,
        '',
        '',
        $manager?->id
      );

      return response()->json([
        'success' => false,
        'errors' => ['mobile-pass-errors' => 'شماره موبایل یا کلمه عبور نادرست است']
      ], 422);
    }

    session(['manager_temp_login' => $manager->id]);
    session(['step3_completed' => true]);

    if ($manager->two_factor_secret_enabled ?? false) {
      return response()->json(['success' => true, 'redirect' => route('admin-two-factor')]);
    }

    Auth::guard('manager')->login($manager);
    LoginLog::create([
      'manager_id' => $manager->id,
      'user_type' => 'manager',
      'login_at' => now(),
      'ip_address' => request()->ip(),
      'device' => request()->header('User-Agent'),
      'login_method' => 'password'
    ]);

    $loginAttempts->resetLoginAttempts($mobile);

    return response()->json(['success' => true, 'redirect' => route('admin.index')]);
  }





  public function twoFactorFormCheck(TwoFactorRequest $request)
  {
    $managerId = session('manager_temp_login');

    if (!$managerId) {
      return response()->json([
        'success' => false,
        'errors' => ['two_factor_secret' => 'دسترسی غیرمجاز'],
        'redirect' => route('admin.auth.login-register-form')
      ], 422);
    }

    $user = Manager::find($managerId);
    if (!$user || !$user->two_factor_secret || !Hash::check($request->two_factor_secret, $user->two_factor_secret)) {
      return response()->json([
        'success' => false,
        'errors' => ['two_factor_secret' => 'کد دو عاملی وارد شده صحیح نمی‌باشد']
      ], 422);
    }

    $user->update(['two_factor_confirmed_at' => Carbon::now()]);

    if ($user instanceof manager) {
      Auth::guard('manager')->login($user);
    } 

    // **ثبت لاگ ورود با دو عاملی**
    LoginLog::create([
      'user_id' => null,
      'manager_id' => $user instanceof manager ? $user->id : null,
      'user_type' => 'manager',
      'login_at' => now(),
      'ip_address' => request()->ip(),
      'device' => request()->header('User-Agent'),
      'login_method' => 'two_factor'
    ]);

    session()->forget(['manager_temp_login']);

    return response()->json([
      'success' => true,
      'redirect' => route('admin.index')
    ]);
  }





  public function loginResendOtp($token)
  {
    $otp = Otp::where('token', $token)->first();

    // اگر توکن موجود نبود یا منقضی شده بود، هدایت به استپ اول با پیام خطا
    if (!$otp || $otp->used || $otp->created_at->addMinutes(2)->isPast()) {
      return response()->json([
        'success' => false,
        'redirect' => route('admin.auth.login-register-form'),
        'message' => 'توکن منقضی شده است. لطفاً دوباره تلاش کنید.'
      ], 410); // 410 Gone
    }

    // ارسال OTP جدید
    return $this->sendOtp($otp->manager);
  }



  public function logout()
  {
    $user = null;
    $guard = null;

    if (Auth::guard('manager')->check()) {
      $user = Auth::guard('manager')->user();
      $guard = 'manager';
      Auth::guard('manager')->logout();
    } 

    if ($user) {
      // به‌روزرسانی لاگ آخرین ورود با مقدار logout_at
      LoginLog::where('manager_id', $guard === 'manager' ? $user->id : null)
        
        ->whereNull('logout_at')
        ->latest()
        ->first()
          ?->update(['logout_at' => now()]);
    }

    return redirect()->route('admin.auth.login-register-form')
      ->with('swal-success', 'شما با موفقیت از سایت خارج شدید');
  }
}
