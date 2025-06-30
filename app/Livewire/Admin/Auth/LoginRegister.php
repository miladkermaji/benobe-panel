<?php

namespace App\Livewire\Admin\Auth;

use App\Models\Otp;
use Livewire\Component;
use App\Models\Secretary;
use Illuminate\Support\Str;
use App\Models\LoginSession;
use App\Models\Admin\Manager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

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
            $this->redirect(route('dr.auth.login-user-pass-form'), navigate: true);
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

        $mobile = preg_replace('/^(\+98|98|0)/', '', $this->mobile);
        $formattedMobile = '0' . $mobile;

        $manager = Manager::where('mobile', $formattedMobile)->first();
        $loginAttempts = new LoginAttemptsService();

        // بررسی وجود کاربر
        if (!$manager) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null, null);
            $this->addError('mobile', 'کاربری با این شماره موبایل وجود ندارد.');
            return;
        }

        $user = $manager;

        // بررسی وضعیت کاربر
        if ($user->status === 0) {
            $loginAttempts->incrementLoginAttempt(
                $user->id,
                $formattedMobile,
                null,
                null,
                $manager ? $manager->id : null
            );
            $this->addError('mobile', 'حساب کاربری شما هنوز تأیید نشده است.');
            return;
        }

        // بررسی قفل بودن حساب
        if ($loginAttempts->isLocked($formattedMobile)) {
            $this->dispatch('rateLimitExceeded', remainingTime: $loginAttempts->getRemainingLockTime($formattedMobile));
            return;
        }

        // افزایش تعداد تلاش‌ها
        $loginAttempts->incrementLoginAttempt(
            $user->id,
            $formattedMobile,
            null,
            null,
            $manager ? $manager->id : null
        );

        session(['step1_completed' => true, 'login_mobile' => $formattedMobile]);

        // بررسی فعال بودن رمز عبور ثابت
        if (($user->static_password_enabled ?? 0) === 1) {
            session(['current_step' => 3]);
            $this->redirect(route('admin.auth.login-user-pass-form'), navigate: true);
            $this->dispatch('pass-form');
            return;
        }

        // ارسال OTP
        $otpCode = rand(1000, 9999);
        $token = Str::random(60);

        Otp::create([
            'token' => $token,
            'manager_id' => $manager ? $user->id : null,
            'otp_code' => $otpCode,
            'login_id' => $user->mobile,
            'type' => 0,
        ]);

        LoginSession::create([
            'token' => $token,
            'manager_id' => $manager ? $user->id : null,
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        // ارسال پیامک
        $messagesService = new MessageService(
            SmsService::create(100285, $user->mobile, [$otpCode])
        );
        $response = $messagesService->send();
        Log::info('SMS send response', ['response' => $response]);

        // ارسال اعلان
        $this->notificationService->sendOtpNotification($user->mobile, $otpCode);

        session(['current_step' => 2, 'otp_token' => $token]);
        $this->dispatch('otpSent', token: $token, otpCode: $otpCode);
        $this->redirect(route('admin.auth.login-confirm-form', ['token' => $token]), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.auth.login-register')
            ->layout('admin.layouts.admin-auth');
    }
}
