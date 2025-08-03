<?php

namespace App\Livewire\Dr\Auth;

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
use Illuminate\Support\Facades\Hash;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;
use App\Services\UserTypeDetectionService;

class DoctorLoginUserPass extends Component
{
    public $password;

    protected $rules = [
        'password' => 'required|string|min:6',
    ];

    protected $messages = [
        'password.required' => 'لطفاً رمز عبور را وارد کنید.',
        'password.min' => 'رمز عبور باید حداقل 6 کاراکتر باشد.',
    ];

    public function mount()
    {
        if (Auth::guard('doctor')->check()) {
            $this->redirect(route('dr-panel'));
        } elseif (Auth::guard('secretary')->check()) {
            $this->redirect(route('dr-panel'));
        }

        // بررسی وجود شماره موبایل در سشن
        if (!session('login_mobile') || !session('step1_completed')) {
            session(['current_step' => 1]);
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        session(['current_step' => 3]);
    }

    public function goBack()
    {
        session(['current_step' => 1]);
        $this->redirect(route('dr.auth.login-register-form'), navigate: true);
    }

    private function formatConditionalTime($seconds)
    {
        if (is_null($seconds) || $seconds < 0) {
            return '0 ثانیه';
        }
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return "$hours ساعت $minutes دقیقه $secs ثانیه";
        } elseif ($minutes > 0) {
            return "$minutes دقیقه $secs ثانیه";
        } else {
            return "$secs ثانیه";
        }
    }

    public function loginWithMobilePass()
    {
        $this->validate();

        $formattedMobile = session('login_mobile');
        $loginAttempts = new LoginAttemptsService();

        // بررسی قفل بودن حساب
        if ($loginAttempts->isLocked($formattedMobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($formattedMobile);
            if ($remainingTime > 0) {
                $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime);
                return;
            }
        }

        $userTypeDetection = new UserTypeDetectionService();
        $userInfo = $userTypeDetection->detectUserTypeForDoctorOnly($formattedMobile);

        if (!$userInfo['model']) {
            $this->addError('password', 'کاربری با این شماره موبایل وجود ندارد.');
            return;
        }

        $user = $userInfo['model'];

        // بررسی فعال بودن قابلیت ورود با رمز عبور
        if (($user->static_password_enabled ?? 0) !== 1) {
            $this->addError('password', 'شما قابلیت ورود با رمز عبور را فعال نکرده‌اید.');
            return;
        }

        // بررسی وضعیت حساب
        if (!$userInfo['is_active']) {
            $this->addError('password', 'حساب کاربری شما غیرفعال است.');
            return;
        }

        // بررسی رمز عبور
        if (!Hash::check($this->password, $user->password)) {
            $loginAttempts->incrementLoginAttempt(
                $userInfo['type'] === 'user' ? $userInfo['model_id'] : null,
                $formattedMobile,
                $userInfo['type'] === 'doctor' ? $userInfo['model_id'] : null,
                $userInfo['type'] === 'secretary' ? $userInfo['model_id'] : null,
                $userInfo['type'] === 'manager' ? $userInfo['model_id'] : null,
                $userInfo['type'] === 'medical_center' ? $userInfo['model_id'] : null
            );
            $this->addError('password', 'رمز عبور نادرست است.');
            $this->dispatch('password-error');
            return;
        }

        // ادامه فرآیند ورود (مانند احراز هویت دو مرحله‌ای یا ورود مستقیم)
        $this->dispatch('password-success');

        // بررسی احراز هویت دو مرحله‌ای
        if (($user->two_factor_secret_enabled ?? 0) === 1) {
            $token = Str::random(60);
            LoginSession::create([
                'token' => $token,
                'doctor_id' => $userInfo['type'] === 'doctor' ? $userInfo['model_id'] : null,
                'secretary_id' => $userInfo['type'] === 'secretary' ? $userInfo['model_id'] : null,
                'medical_center_id' => $userInfo['type'] === 'medical_center' ? $userInfo['model_id'] : null,
                'step' => 2,
                'expires_at' => now()->addMinutes(10),
            ]);

            $otpCode = rand(1000, 9999);
            Otp::create([
                'token' => $token,
                'otp_code' => $otpCode,
                'login_id' => $user->mobile ?? $user->phone_number,
                'type' => 0,
                'otpable_type' => $userInfo['model_class'],
                'otpable_id' => $userInfo['model_id'],
            ]);

            $messagesService = new MessageService(SmsService::create(100286, $user->mobile ?? $user->phone_number, [$otpCode]));
            $response = $messagesService->send();
            Log::info('SMS send response', ['response' => $response]);

            session(['current_step' => 2, 'otp_token' => $token]);
            $this->dispatch('otpSent', token: $token);
            $this->redirect(route('dr.auth.login-confirm-form', ['token' => $token]), navigate: true);
            return;
        }

        // ایجاد توکن برای جلسه ورود
        $token = Str::random(60);
        LoginSession::create([
            'token' => $token,
            'doctor_id' => $userInfo['type'] === 'doctor' ? $userInfo['model_id'] : null,
            'secretary_id' => $userInfo['type'] === 'secretary' ? $userInfo['model_id'] : null,
            'medical_center_id' => $userInfo['type'] === 'medical_center' ? $userInfo['model_id'] : null,
            'step' => 3,
            'expires_at' => now()->addMinutes(10),
        ]);

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
            Auth::guard('medical_center')->login($user);
            $redirectRoute = route('mc-panel');
            $userType = 'medical_center';
        }

        // ثبت لاگ ورود
        LoginLog::create([
            'doctor_id' => $user instanceof Doctor ? $user->id : null,
            'secretary_id' => $user instanceof Secretary ? $user->id : null,
            'medical_center_id' => $user instanceof MedicalCenter ? $user->id : null,
            'user_type' => $userType,
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'device' => request()->header('User-Agent'),
            'login_method' => 'password',
        ]);

        // ریست کردن تعداد تلاش‌ها پس از ورود موفق
        $loginAttempts->resetLoginAttempts($formattedMobile);

        session()->forget(['step1_completed', 'login_mobile']);
        $this->dispatch('loginSuccess');
        $this->redirect($redirectRoute);
    }

    public function render()
    {
        return view('livewire.dr.auth.doctor-login-user-pass')->layout('dr.layouts.dr-auth');
        ;
    }
}
