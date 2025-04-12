<?php

namespace App\Livewire\Admin\Auth;

use App\Models\Otp;
use Livewire\Component;
use App\Models\LoginLog;
use App\Models\Secretary;
use Illuminate\Support\Str;
use App\Models\LoginSession;
use App\Models\Admin\Manager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class LoginUserPass extends Component
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
        if (Auth::guard('manager')->check()) {
            $this->redirect(route('admin-panel'));
        }

        // بررسی وجود شماره موبایل در سشن
        if (!session('login_mobile') || !session('step1_completed')) {
            session(['current_step' => 1]);
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
            return;
        }

        session(['current_step' => 3]);
    }

    public function goBack()
    {
        session(['current_step' => 1]);
        $this->redirect(route('admin.auth.login-register-form'), navigate: true);
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
            $this->dispatch('rateLimitExceeded', remainingTime: $loginAttempts->getRemainingLockTime($formattedMobile));
            return;
        }

        $manager = Manager::where('mobile', $formattedMobile)->first();

        if (!$manager) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null, null);
            $this->addError('password', 'کاربری با این شماره موبایل وجود ندارد.');
            return;
        }

        $user = $manager;

        // بررسی فعال بودن قابلیت ورود با رمز عبور
        if (($user->static_password_enabled ?? 0) !== 1) {
            $loginAttempts->incrementLoginAttempt(
                $user->id,
                $formattedMobile,
                null,
                null,
                $manager ? $manager->id : null,
            );
            $this->addError('password', 'شما قابلیت ورود با رمز عبور را فعال نکرده‌اید.');
            return;
        }

        // بررسی رمز عبور و وضعیت حساب
        if (!Hash::check($this->password, $user->password) || $user->status !== 1) {

            $loginAttempts->incrementLoginAttempt(
                $user->id,
                $formattedMobile,
                null,
                null,
                $manager ? $manager->id : null,
            );

            $this->addError('password', 'رمز عبور نادرست است یا حساب غیرفعال است.');
            $this->dispatch('password-error');

            return;
        }

        $this->dispatch('password-success');

        // بررسی احراز هویت دو مرحله‌ای
        if (($user->two_factor_enabled ?? 0) === 1) {
            $token = Str::random(60);
            LoginSession::create([
                'token' => $token,
                'manager_id' => $manager ? $user->id : null,
                'step' => 2,
                'expires_at' => now()->addMinutes(10),
            ]);

            $otpCode = rand(1000, 9999);
            Otp::create([
                'token' => $token,
                'manager_id' => $manager ? $user->id : null,
                'otp_code' => $otpCode,
                'login_id' => $user->mobile,
                'type' => 0,
            ]);

            $messagesService = new MessageService(
                SmsService::create(100253, $user->mobile, [$otpCode])
            );
            $response = $messagesService->send();
            Log::info('SMS send response', ['response' => $response]);

            session(['current_step' => 2, 'otp_token' => $token]);
            $this->dispatch('otpSent', token: $token);
            $this->redirect(route('admin.auth.login-confirm-form', ['token' => $token]), navigate: true);
            return;
        }

        // ایجاد توکن برای جلسه ورود
        $token = Str::random(60);
        LoginSession::create([
            'token' => $token,
            'manager_id' => $manager ? $user->id : null,
            'step' => 3,
            'expires_at' => now()->addMinutes(10),
        ]);

        // ورود با گارد مناسب
        if ($user instanceof Manager) {
            Auth::guard('manager')->login($user);
            $redirectRoute = route('admin-panel');
            $userType = 'manager';
        }

        // ثبت لاگ ورود
        LoginLog::create([
            'manager_id' => $user instanceof Manager ? $user->id : null,
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
        return view('livewire.admin.auth.login-user-pass')->layout('admin.layouts.admin-auth');
    }
}
