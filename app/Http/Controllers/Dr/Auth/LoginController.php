<?php
namespace App\Http\Controllers\Dr\Auth;
use Carbon\Carbon;
use App\Models\Otp;
use App\Models\LoginLog;
use App\Models\Dr\Doctor;
use Illuminate\Support\Str;
use App\Models\Dr\Secretary;
use Illuminate\Http\Request;
use App\Models\Dr\LoginAttempt;
use App\Http\Requests\MobileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\TwoFactorRequest;
use App\Http\Requests\MobilePassRequest;
use App\Http\Requests\OtpDoctorsRequest;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class LoginController
{
  public function loginRegisterForm()
  {
    return view('dr.auth.login');
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
    return view('dr.auth.login', ['step' => 3]);
  }

  public function twoFactorForm()
  {
    // بررسی اینکه آیا کاربر از استپ 3 آمده است یا خیر
    if (!session()->has('step3_completed')) {
      return redirect()->route('dr.auth.login-user-pass-form');
    }
    return view('dr.auth.login', ['step' => 4]);
  }

  public function loginConfirmForm($token)
  {
    if (!session()->has('step1_completed')) {
      return redirect()->route('dr.auth.login-register-form');
    }

    $otp = Otp::where('token', $token)->first();

    $remainingTime = $otp
      ? max(0, (int) ($otp->created_at->addMinutes(2)->timestamp - now()->timestamp) * 1000)
      : 0;

    return view('dr.auth.login', [
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

    $doctor = Doctor::where('mobile', $formattedMobile)->first();
    $secretary = Secretary::where('mobile', $formattedMobile)->first();

    $loginAttempts = new LoginAttemptsService();

    if (!$doctor && !$secretary) {
      $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null);
      return response()->json([
        'success' => false,
        'errors' => ['mobile' => ['کاربری با این شماره تلفن وجود ندارد.']]
      ], 422);
    }

    if (($doctor && $doctor->status !== 1) || ($secretary && $secretary->status !== 1)) {
      $userId = $doctor?->id ?? $secretary?->id ?? null;
      $loginAttempts->incrementLoginAttempt($userId, $formattedMobile, $doctor?->id, $secretary?->id);
      return response()->json([
        'success' => false,
        'errors' => ['mobile' => ['حساب کاربری فعال نیست.']]
      ], 422);
    }

    if ($loginAttempts->isLocked($formattedMobile)) {
      return $this->handleRateLimitError($formattedMobile);
    }

    $userId = $doctor?->id ?? $secretary?->id ?? null;
    $loginAttempts->incrementLoginAttempt($userId, $formattedMobile, $doctor?->id, $secretary?->id);

    session(['step1_completed' => true]);

    return $this->sendOtp($doctor ?? $secretary);
  }




  private function sendOtp($user)
  {
    $otpCode = rand(1000, 9999); // تولید کد OTP
    $token = Str::random(60); // تولید توکن

    // ذخیره اطلاعات OTP در دیتابیس
    Otp::create([
      'token' => $token,
      'doctor_id' => $user instanceof Doctor ? $user->id : null,
      'secretary_id' => $user instanceof Secretary ? $user->id : null,
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
      $mobile = $otp?->doctor?->mobile ?? $otp?->secretary?->mobile ?? null;
      $userId = $otp?->doctor_id ?? $otp?->secretary_id ?? null;

      (new LoginAttemptsService())->incrementLoginAttempt($userId, $mobile, $otp?->doctor_id, $otp?->secretary_id);
      return response()->json([
        'success' => false,
        'errors' => ['otp-code' => ['کد وارد شده صحیح نمی‌باشد']]
      ], 422);
    }

    $otp->update(['used' => 1]);

    $user = $otp->doctor ?? $otp->secretary;

    if (empty($user->mobile_verified_at)) {
      $user->update(['mobile_verified_at' => Carbon::now()]);
    }

    // انتخاب گارد مناسب
    if ($user instanceof Doctor) {
      Auth::guard('doctor')->login($user);
    } elseif ($user instanceof Secretary) {
      Auth::guard('secretary')->login($user);
    }

    (new LoginAttemptsService())->resetLoginAttempts($user->mobile);

    session()->forget('step1_completed');
    LoginLog::create([
      'user_id' => null,
      'doctor_id' => $user instanceof Doctor ? $user->id : null,
      'secretary_id' => $user instanceof Secretary ? $user->id : null,
      'user_type' => $user instanceof Doctor ? 'doctor' : 'secretary',
      'login_at' => now(),
      'ip_address' => request()->ip(),
      'device' => request()->header('User-Agent')
    ]);
    return response()->json([
      'success' => true,
      'redirect' => route('dr-panel')
    ]);
  }


  public function loginWithMobilePass(MobilePassRequest $request)
  {
    $mobile = $request->mobile;
    $doctor = Doctor::where('mobile', $mobile)->first();
    $secretary = Secretary::where('mobile', $mobile)->first();

    $loginAttempts = new LoginAttemptsService();

    if ($loginAttempts->isLocked($mobile)) {
      return $this->handleRateLimitError($mobile);
    }

    $user = $doctor ?? $secretary;

    if (!$user || !Hash::check($request->password, $user->password) || ($user->status ?? 0) !== 1) {
      $loginAttempts->incrementLoginAttempt($user?->id ?? null, $mobile, $doctor?->id, $secretary?->id);
      return response()->json([
        'success' => false,
        'errors' => ['mobile-pass-errors' => 'شماره موبایل یا کلمه عبور نادرست است']
      ], 422);
    }

    session(['doctor_temp_login' => $doctor?->id, 'secretary_temp_login' => $secretary?->id]);
    session(['step3_completed' => true]);

    // **اگر کاربر احراز هویت دو عاملی دارد، باید ابتدا تایید شود**
    if ($user->two_factor_secret_enabled ?? false) {
      return response()->json([
        'success' => true,
        'redirect' => route('dr-two-factor')
      ]);
    }

    //  ورود نهایی بدون احراز هویت دو عاملی
    if ($user instanceof Doctor) {
      Auth::guard('doctor')->login($user);
    } elseif ($user instanceof Secretary) {
      Auth::guard('secretary')->login($user);
    }

    // **ثبت لاگ ورود با رمز عبور**
    LoginLog::create([
      'user_id' => null,
      'doctor_id' => $user instanceof Doctor ? $user->id : null,
      'secretary_id' => $user instanceof Secretary ? $user->id : null,
      'user_type' => $user instanceof Doctor ? 'doctor' : 'secretary',
      'login_at' => now(),
      'ip_address' => request()->ip(),
      'device' => request()->header('User-Agent'),
      'login_method' => 'password'
    ]);

    $loginAttempts->resetLoginAttempts($mobile);

    return response()->json([
      'success' => true,
      'redirect' => route('dr-panel')
    ]);
  }




  public function twoFactorFormCheck(TwoFactorRequest $request)
  {
    $doctorId = session('doctor_temp_login');
    $secretaryId = session('secretary_temp_login');

    if (!$doctorId && !$secretaryId) {
      return response()->json([
        'success' => false,
        'errors' => ['two_factor_secret' => 'دسترسی غیرمجاز'],
        'redirect' => route('dr.auth.login-register-form')
      ], 422);
    }

    $user = Doctor::find($doctorId) ?? Secretary::find($secretaryId);

    if (!$user || !$user->two_factor_secret || !Hash::check($request->two_factor_secret, $user->two_factor_secret)) {
      return response()->json([
        'success' => false,
        'errors' => ['two_factor_secret' => 'کد دو عاملی وارد شده صحیح نمی‌باشد']
      ], 422);
    }

    $user->update(['two_factor_confirmed_at' => Carbon::now()]);

    if ($user instanceof Doctor) {
      Auth::guard('doctor')->login($user);
    } elseif ($user instanceof Secretary) {
      Auth::guard('secretary')->login($user);
    }

    // **ثبت لاگ ورود با دو عاملی**
    LoginLog::create([
      'user_id' => null,
      'doctor_id' => $user instanceof Doctor ? $user->id : null,
      'secretary_id' => $user instanceof Secretary ? $user->id : null,
      'user_type' => $user instanceof Doctor ? 'doctor' : 'secretary',
      'login_at' => now(),
      'ip_address' => request()->ip(),
      'device' => request()->header('User-Agent'),
      'login_method' => 'two_factor'
    ]);

    session()->forget(['doctor_temp_login', 'secretary_temp_login']);

    return response()->json([
      'success' => true,
      'redirect' => route('dr-panel')
    ]);
  }





  public function loginResendOtp($token)
  {
    $otp = Otp::where('token', $token)->first();

    // بررسی معتبر بودن OTP
    if (!$otp || $otp->used) {
      return response()->json(['error' => 'آدرس وارد شده نامعتبر است'], 422);
    }

    // بازیابی کاربر
    $user = $otp->doctor ?? $otp->secretary;

    // ارسال OTP جدید
    return $this->sendOtp($user);
  }


  public function logout()
  {
    $user = null;
    $guard = null;

    if (Auth::guard('doctor')->check()) {
      $user = Auth::guard('doctor')->user();
      $guard = 'doctor';
      Auth::guard('doctor')->logout();
    } elseif (Auth::guard('secretary')->check()) {
      $user = Auth::guard('secretary')->user();
      $guard = 'secretary';
      Auth::guard('secretary')->logout();
    }

    if ($user) {
      // به‌روزرسانی لاگ آخرین ورود با مقدار logout_at
      LoginLog::where('doctor_id', $guard === 'doctor' ? $user->id : null)
        ->where('secretary_id', $guard === 'secretary' ? $user->id : null)
        ->whereNull('logout_at')
        ->latest()
        ->first()
          ?->update(['logout_at' => now()]);
    }

    return redirect()->route('dr.auth.login-register-form')
      ->with('swal-success', 'شما با موفقیت از سایت خارج شدید');
  }


}