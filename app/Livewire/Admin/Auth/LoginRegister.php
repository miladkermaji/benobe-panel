<?php

namespace App\Livewire\Admin\Auth;

use App\Models\Otp;
use Livewire\Component;
use App\Models\Secretary;
use Illuminate\Support\Str;
use App\Models\LoginSession;
use App\Models\Manager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;
use App\Services\UserTypeDetectionService;

class LoginRegister extends Component
{
    public $mobile;
    protected $notificationService;

    public function booted()
    {
        $this->notificationService = new NotificationService();
    }

    public function mount()
    {
        if (Auth::guard('manager')->check()) {
            $this->redirect(route('admin-panel'));
        } elseif (session('current_step') === 2) {
            $this->redirect(route('admin.auth.login-confirm-form', ['token' => session('otp_token')]), navigate: true);
        } elseif (session('current_step') === 3) {
            $this->redirect(route('admin.auth.login-user-pass-form'), navigate: true);
        } elseif (session('current_step') === 4) {
            $this->redirect(route('admin.auth.login-set-password-form'), navigate: true);
        }
        session(['current_step' => 1]);
    }

    private function formatTime($seconds)
    {
        if (is_null($seconds) || $seconds < 0) {
            return '0 دقیقه و 0 ثانیه';
        }
        $minutes = floor($seconds / 60);
        $remainingSeconds = round($seconds % 60);
        return "$minutes دقیقه و $remainingSeconds ثانیه";
    }

    public function loginRegister()
    {
        $this->validate([
            'mobile' => [
                'required',
                'string',
                'regex:/^(?!09{1}(\d)\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)\d{7}$/',
            ],
        ], [
            'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل باید فرمت معتبر داشته باشد (مثلاً 09181234567).',
        ]);

        $userTypeDetection = new UserTypeDetectionService();
        $userInfo = $userTypeDetection->detectUserTypeForAdminOnly($this->mobile);
        $formattedMobile = $userTypeDetection->formatMobile($this->mobile);

        $loginAttempts = new LoginAttemptsService();

        // بررسی وجود کاربر (فقط مدیر)
        if (!$userInfo['model']) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null, null);
            $this->addError('mobile', 'کاربری با این شماره موبایل وجود ندارد.');
            return;
        }

        $user = $userInfo['model'];

        // بررسی وضعیت کاربر
        if (!$userInfo['is_active']) {
            $loginAttempts->incrementLoginAttempt(
                $userInfo['model_id'],
                $formattedMobile,
                null,
                null,
                $userInfo['model_id']
            );
            $this->addError('mobile', 'حساب کاربری شما هنوز تأیید نشده است.');
            return;
        }

        // بررسی قفل بودن حساب
        if ($loginAttempts->isLocked($formattedMobile)) {
            $this->dispatch('rateLimitExceeded', remainingTime: $loginAttempts->getRemainingLockTime($formattedMobile));
            return;
        }

        session(['step1_completed' => true, 'login_mobile' => $formattedMobile]);

        // Debug: بررسی وضعیت static_password_enabled
        \Log::info('LoginRegister Debug', [
            'mobile' => $formattedMobile,
            'static_password_enabled' => $user->static_password_enabled,
            'user_id' => $user->id
        ]);

        // بررسی فعال بودن رمز عبور ثابت
        if ($user->static_password_enabled === true) {
            // اگر رمز عبور ثابت فعال است، به مرحله رمز عبور برو
            session(['current_step' => 3]);
            $this->redirect(route('admin.auth.login-user-pass-form'), navigate: true);
            $this->dispatch('pass-form');
            return;
        }

        // اگر رمز عبور ثابت فعال نیست، به مرحله تعیین رمز عبور برو
        session(['current_step' => 4]);
        $this->redirect(route('admin.auth.login-set-password-form'), navigate: true);
        $this->dispatch('set-password-form');
        return;
    }

    public function render()
    {
        return view('livewire.admin.auth.login-register')
            ->layout('admin.layouts.admin-auth');
    }
}
