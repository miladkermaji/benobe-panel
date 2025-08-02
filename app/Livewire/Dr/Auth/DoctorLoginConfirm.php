<?php

namespace App\Livewire\Dr\Auth;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\Doctor;
use Livewire\Component;
use App\Models\LoginLog;
use App\Models\Secretary;
use App\Models\MedicalCenter;
use Illuminate\Support\Str;
use App\Models\LoginSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class DoctorLoginConfirm extends Component
{
    public $token;
    public $otpCode = ['', '', '', ''];
    public $remainingTime;
    public $countDownDate;
    public $showResendButton = false;

    public function mount($token)
    {
        $this->token = $token;
        $loginSession = LoginSession::where('token', $token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            session(['current_step' => 1]);
            $this->dispatch('otpExpired');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $token)->first();
        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp?->doctor?->mobile ?? $otp?->secretary?->mobile ?? $otp?->medicalCenter?->phone_number ?? $otp->login_id ?? 'unknown';

        if ($otp) {
            $this->countDownDate = $otp->created_at->addMinutes(2)->timestamp * 1000;
            $this->remainingTime = max(0, $this->countDownDate - now()->timestamp * 1000);
            $this->showResendButton = $this->remainingTime <= 0 && !$loginAttempts->isLocked($mobile);
        } else {
            $this->remainingTime = 0;
            $this->countDownDate = 0;
            $this->showResendButton = !$loginAttempts->isLocked($mobile);
        }

        $this->dispatch('initTimer', [
            'remainingTime' => $this->remainingTime,
            'countDownDate' => $this->countDownDate,
            'showResendButton' => $this->showResendButton,
            'token' => $this->token,
        ]);
    }

    public function goBack()
    {
        session(['current_step' => 1, 'from_back' => true]);

        $this->dispatch('navigateTo', url: route('dr.auth.login-register-form'));
    }

    public function loginConfirm()
    {
        Log::info("=== LOGIN CONFIRM STARTED ===");

        Log::info("Login confirm started", [
            'token' => $this->token,
            'otp_code' => $this->otpCode
        ]);

        $otpCode = strrev(implode('', $this->otpCode));

        if (empty(trim($otpCode))) {
            $this->addError('otpCode', 'لطفاً کد تأیید را وارد کنید.');
            return;
        }

        if (strlen($otpCode) < 4) {
            $this->addError('otpCode', 'کد تأیید باید ۴ رقم باشد.');
            return;
        }

        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            $this->addError('otpCode', 'توکن منقضی شده یا نامعتبر است.');
            $this->dispatch('otpExpired');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $this->token)
            ->where('used', 0)
            ->where('created_at', '>=', Carbon::now()->subMinutes(2))
            ->first();

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp?->doctor?->mobile ?? $otp?->secretary?->mobile ?? $otp?->medicalCenter?->phone_number ?? $otp->login_id ?? 'unknown';

        if ($loginAttempts->isLocked($mobile)) {
            $this->dispatch('rateLimitExceeded', remainingTime: $loginAttempts->getRemainingLockTime($mobile));
            return;
        }

        if (!$otp) {
            $this->addError('otpCode', 'کد تأیید نامعتبر یا منقضی شده است.');
            $this->showResendButton = !$loginAttempts->isLocked($mobile);
            return;
        }

        if ($otp->otp_code !== $otpCode) {
            $userId = $otp->doctor_id ?? $otp->secretary_id ?? $otp->medical_center_id ?? null;
            $loginAttempts->incrementLoginAttempt(
                $userId,
                $mobile,
                $otp->doctor_id,
                $otp->secretary_id,
                null,
                $otp->medical_center_id
            );
            $this->addError('otpCode', 'کد تأیید وارد شده صحیح نیست.');
            return;
        }

        $otp->update(['used' => 1]);
        $user = $otp->doctor ?? $otp->secretary ?? $otp->medicalCenter;

        if (empty($user->mobile_verified_at)) {
            $user->update(['mobile_verified_at' => Carbon::now()]);
        }

        // ورود با گارد مناسب و تعیین مسیر هدایت
        if ($user instanceof Doctor) {
            Auth::guard('doctor')->login($user);
            $redirectRoute = route('dr-panel');
            $userType = 'doctor';
        } elseif ($user instanceof Secretary) {
            Auth::guard('secretary')->login($user);
            $redirectRoute = route('dr-panel');
            $userType = 'secretary';
        } else {
            // Medical Center - redirect to MC panel
            Log::info("Attempting to login medical center", [
                'user_id' => $user->id,
                'phone_number' => $user->phone_number,
                'is_active' => $user->is_active
            ]);

            // تنظيف الـ session قبل تسجيل الدخول
            session()->flush();

            Auth::guard('medical_center')->login($user);

            // حفظ بيانات المستخدم في الـ session
            session()->put('medical_center_user', $user);

            // تجديد الـ session بعد تسجيل الدخول
            session()->regenerate();

            // حفظ الـ session بعد تجديدها
            session()->save();

            // مقدار مسیر ریدایرکت را به صورت دستی ست می‌کنم تا مشکل حل شود
            $redirectRoute = '/mc/panel';
            $userType = 'medical_center';
        }

        Log::info("Login successful", [
            'user_type' => $userType,
            'user_id' => $user->id,
            'redirect_route' => $redirectRoute,
            'guard_check' => Auth::guard('medical_center')->check(),
            'session_id' => session()->getId()
        ]);
        Log::info("Resetting login attempts for mobile: $mobile");
        $loginAttempts->resetLoginAttempts($user->mobile ?? $user->phone_number);
        session()->forget(['step1_completed', 'current_step', 'otp_token']);
        LoginSession::where('token', $this->token)->delete();

        LoginLog::create([
            'doctor_id' => $user instanceof Doctor ? $user->id : null,
            'secretary_id' => $user instanceof Secretary ? $user->id : null,
            'medical_center_id' => $user instanceof MedicalCenter ? $user->id : null,
            'user_type' => $userType,
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'device' => request()->header('User-Agent'),
        ]);

        $this->dispatch('loginSuccess');

        Log::info("About to redirect", [
            'redirect_route' => $redirectRoute,
            'user_type' => $userType,
            'session_id' => session()->getId()
        ]);

        // حفظ الـ session قبل إعادة التوجيه
        session()->save();

        $this->redirect($redirectRoute, navigate: true);
    }

    public function resendOtp()
    {
        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            $this->dispatch('otpExpired');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $this->token)->first();
        if (!$otp) {
            $this->dispatch('otpExpired');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp->doctor?->mobile ?? $otp->secretary?->mobile ?? $otp->medicalCenter?->phone_number ?? $otp->login_id ?? 'unknown';

        // بررسی قفل بودن حساب
        if ($loginAttempts->isLocked($mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
            if ($remainingTime > 0) {
                $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime);
                return;
            }
        }

        $otpCode = rand(1000, 9999);
        $newToken = Str::random(60);

        Otp::create([
            'token' => $newToken,
            'doctor_id' => $otp->doctor_id,
            'secretary_id' => $otp->secretary_id,
            'medical_center_id' => $otp->medical_center_id,
            'otp_code' => $otpCode,
            'login_id' => $mobile,
            'type' => 0,
        ]);

        LoginSession::where('token', $this->token)->delete();
        LoginSession::create([
            'token' => $newToken,
            'doctor_id' => $otp->doctor_id,
            'secretary_id' => $otp->secretary_id,
            'medical_center_id' => $otp->medical_center_id,
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $user = $otp->doctor ?? $otp->secretary ?? $otp->medicalCenter;
        $messagesService = new MessageService(
            SmsService::create(100286, $user->mobile ?? $user->phone_number, [$otpCode])
        );
        $messagesService->send();

        $this->token = $newToken;
        $this->remainingTime = 120000;
        $this->countDownDate = now()->addMinutes(2)->timestamp * 1000;
        $this->showResendButton = false;
        $this->otpCode = ['', '', '', ''];
        session(['otp_token' => $newToken]);

        $this->dispatch(
            'otpResent',
            message: 'کد جدید ارسال شد',
            remainingTime: $this->remainingTime,
            countDownDate: $this->countDownDate,
            showResendButton: false,
            token: $newToken,
        );
    }

    public function render()
    {
        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            session(['current_step' => 1]);
            $this->dispatch('otpExpired');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        return view('livewire.dr.auth.doctor-login-confirm', [
            'remainingTime' => $this->remainingTime,
            'countDownDate' => $this->countDownDate,
            'showResendButton' => $this->showResendButton,
        ])->layout('dr.layouts.dr-auth');
    }
}
