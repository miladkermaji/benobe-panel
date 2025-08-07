<?php

namespace App\Livewire\Admin\Auth;

use App\Models\Manager;
use App\Models\Otp;
use App\Models\LoginSession;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Services\UserTypeDetectionService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class LoginSetPassword extends Component
{
    public $password = '';
    public $password_confirmation = '';
    protected $notificationService;

    public function booted()
    {
        $this->notificationService = new NotificationService();
    }

    protected $rules = [
        'password' => [
            'required',
            'string',
            'min:8',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ],
        'password_confirmation' => 'required|same:password',
    ];

    protected $messages = [
        'password.required' => 'لطفاً رمز عبور را وارد کنید.',
        'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
        'password.regex' => 'رمز عبور باید شامل حداقل یک حرف بزرگ انگلیسی، یک حرف کوچک، یک عدد و یک کاراکتر خاص باشد.',
        'password_confirmation.required' => 'لطفاً تکرار رمز عبور را وارد کنید.',
        'password_confirmation.same' => 'تکرار رمز عبور مطابقت ندارد.',
    ];

    public function mount()
    {
        // بررسی وجود شماره موبایل در سشن
        if (!session('login_mobile') || !session('step1_completed')) {
            session(['current_step' => 1]);
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
            return;
        }

        session(['current_step' => 4]);
    }

    public function goBack()
    {
        session(['current_step' => 1]);
        $this->redirect(route('admin.auth.login-register-form'), navigate: true);
    }

    public function setPassword()
    {
        $this->validate();

        $formattedMobile = session('login_mobile');
        $userTypeDetection = new UserTypeDetectionService();
        $userInfo = $userTypeDetection->detectUserTypeForAdminOnly($formattedMobile);

        if (!$userInfo['model']) {
            $this->addError('password', 'کاربری با این شماره موبایل وجود ندارد.');
            return;
        }

        $user = $userInfo['model'];

        // بررسی وضعیت کاربر
        if (!$userInfo['is_active']) {
            $this->addError('password', 'حساب کاربری شما غیرفعال است.');
            return;
        }

        // بروزرسانی رمز عبور
        $user->update([
            'password' => Hash::make($this->password),
            'static_password_enabled' => true,
        ]);

        // ارسال OTP
        $otpCode = rand(1000, 9999);
        $token = Str::random(60);

        Otp::create([
            'token' => $token,
            'otp_code' => $otpCode,
            'login_id' => $user->mobile,
            'type' => 0,
            'otpable_type' => $userInfo['model_class'],
            'otpable_id' => $userInfo['model_id'],
        ]);

        LoginSession::create([
            'token' => $token,
            'sessionable_type' => $userInfo['model_class'],
            'sessionable_id' => $userInfo['model_id'],
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        // ارسال پیامک
        $messagesService = new MessageService(
            SmsService::create(100286, $user->mobile, [$otpCode])
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
        return view('livewire.admin.auth.login-set-password')->layout('admin.layouts.admin-auth');
    }
}
