<?php

namespace App\Livewire\Dr\Auth;

use App\Models\Otp;
use App\Models\Doctor;
use Livewire\Component;
use App\Models\Secretary;
use App\Models\MedicalCenter;
use Illuminate\Support\Str;
use App\Models\LoginSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class DoctorLoginRegister extends Component
{
    public $mobile;
    protected $notificationService;

    public function booted()
    {
        $this->notificationService = new NotificationService();
    }

    public function mount()
    {
        if (Auth::guard('doctor')->check()) {
            $this->redirect(route('dr-panel'));
        } elseif (Auth::guard('secretary')->check()) {
            $this->redirect(route('dr-panel'));
        } elseif (session('current_step') === 2) {
            $this->redirect(route('dr.auth.login-confirm-form', ['token' => session('otp_token')]), navigate: true);
        } elseif (session('current_step') === 3) {
            $this->redirect(route('dr.auth.login-user-pass-form'), navigate: true);
        }

        session(['current_step' => 1]);
        session()->forget('from_back'); // حذف فلگ پس از استفاده
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
        if (!$this->mobile) { // جلوگیری از سابمیت با ورودی خالی
            return;
        }
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

        $doctor = Doctor::where('mobile', $formattedMobile)->first();
        $secretary = Secretary::where('mobile', $formattedMobile)->first();
        $medicalCenter = MedicalCenter::where('phone_number', $formattedMobile)->first();
        $loginAttempts = new LoginAttemptsService();

        // بررسی وجود کاربر
        if (!$doctor && !$secretary && !$medicalCenter) {
            $this->addError('mobile', 'کاربری با این شماره موبایل وجود ندارد.');
            return;
        }

        $user = $doctor ?? $secretary ?? $medicalCenter;

        // بررسی وضعیت کاربر
        if ($user->status === 0 || ($user instanceof MedicalCenter && !$user->is_active)) {
            $this->addError('mobile', 'حساب کاربری شما هنوز تأیید نشده است.');
            return;
        }

        // بررسی قفل بودن حساب
        if ($loginAttempts->isLocked($formattedMobile)) {
            $this->dispatch('rateLimitExceeded', remainingTime: $loginAttempts->getRemainingLockTime($formattedMobile));
            return;
        }

        session(['step1_completed' => true, 'login_mobile' => $formattedMobile]);

        // بررسی فعال بودن رمز عبور ثابت
        if (($user->static_password_enabled ?? 0) === 1) {
            session(['current_step' => 3]);
            $this->redirect(route('dr.auth.login-user-pass-form'), navigate: true);
            $this->dispatch('pass-form');
            return;
        }

        // ارسال OTP
        $otpCode = rand(1000, 9999);
        $token = Str::random(60);

        Otp::create([
            'token' => $token,
            'doctor_id' => $doctor ? $doctor->id : null,
            'secretary_id' => $secretary ? $secretary->id : null,
            'medical_center_id' => $medicalCenter ? $medicalCenter->id : null,
            'otp_code' => $otpCode,
            'login_id' => $user->mobile ?? $user->phone_number,
            'type' => 0,
        ]);

        LoginSession::create([
            'token' => $token,
            'doctor_id' => $doctor ? $doctor->id : null,
            'secretary_id' => $secretary ? $secretary->id : null,
            'medical_center_id' => $medicalCenter ? $medicalCenter->id : null,
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        // ارسال پیامک
        $messagesService = new MessageService(
            SmsService::create(100286, $user->mobile ?? $user->phone_number, [$otpCode])
        );
        $response = $messagesService->send();
        Log::info('SMS send response', ['response' => $response]);

        // ارسال اعلان
        $this->notificationService->sendOtpNotification($user->mobile ?? $user->phone_number, $otpCode);

        session(['current_step' => 2, 'otp_token' => $token]);
        $this->dispatch('otpSent', token: $token, otpCode: $otpCode);
        $this->redirect(route('dr.auth.login-confirm-form', ['token' => $token]), navigate: true);
    }

    public function render()
    {
        return view('livewire.dr.auth.doctor-login-register')
            ->layout('dr.layouts.dr-auth');
    }
}
