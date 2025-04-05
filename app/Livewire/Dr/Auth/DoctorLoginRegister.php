<?php
namespace App\Livewire\Dr\Auth;

use App\Http\Services\LoginAttemptsService\LoginAttemptsService;
use App\Models\Doctor;
use App\Models\LoginSession;
use App\Models\Otp;
use App\Models\Secretary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class DoctorLoginRegister extends Component
{
    public $mobile;

    public function mount()
    {
        // چک کردن اینکه آیا کاربر (دکتر یا منشی) قبلاً لاگین کرده
        if (Auth::guard('doctor')->check()) {
            $this->redirect(route('dr-panel'));
        } elseif (Auth::guard('secretary')->check()) {
            $this->redirect(route('dr-panel')); // فرض می‌کنم پنل منشی dr-panel باشه
        } elseif (session('current_step') === 2) {
            $this->redirect(route('dr.auth.login-confirm-form', ['token' => session('otp_token')]), navigate: true);
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
        $minutes          = floor($seconds / 60);
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
            'mobile.regex'    => 'شماره موبایل باید فرمت معتبر داشته باشد (مثلاً 09181234567).',
        ]);

        $mobile          = preg_replace('/^(\+98|98|0)/', '', $this->mobile);
        $formattedMobile = '0' . $mobile;

        // چک کردن هر دو مدل Doctor و Secretary
        $doctor        = Doctor::where('mobile', $formattedMobile)->first();
        $secretary     = Secretary::where('mobile', $formattedMobile)->first();
        $loginAttempts = new LoginAttemptsService();

        // اگر هیچ کاربری پیدا نشد
        if (! $doctor && ! $secretary) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null, null);
            $this->addError('mobile', 'کاربری با این شماره تلفن وجود ندارد.');
            return;
        }

        // انتخاب کاربر (دکتر یا منشی)
        $user = $doctor ?? $secretary;

        // چک کردن وضعیت فعال بودن
        if ($user->status !== 1) {
            $loginAttempts->incrementLoginAttempt(
                $user->id,
                $formattedMobile,
                $doctor ? $doctor->id : null,
                $secretary ? $secretary->id : null,
                null
            );
            $this->addError('mobile', 'حساب کاربری شما فعال نیست.');
            return;
        }

        // چک کردن قفل شدن به دلیل تلاش زیاد
        if ($loginAttempts->isLocked($formattedMobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTimeFormatted($formattedMobile);
            $formattedTime = $this->formatTime($remainingTime);
            $this->addError('mobile', "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.");
            $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime);
            return;
        }

        // ثبت تلاش ورود
        $loginAttempts->incrementLoginAttempt(
            $user->id,
            $formattedMobile,
            $doctor ? $doctor->id : null,
            $secretary ? $secretary->id : null,
            null
        );
        session(['step1_completed' => true]);

        $otpCode = rand(1000, 9999);
        $token   = Str::random(60);

        // ثبت OTP بر اساس نوع کاربر
        Otp::create([
            'token'        => $token,
            'doctor_id'    => $doctor ? $user->id : null,
            'secretary_id' => $secretary ? $user->id : null,
            'otp_code'     => $otpCode,
            'login_id'     => $user->mobile,
            'type'         => 0,
        ]);

        // ثبت LoginSession بر اساس نوع کاربر
        LoginSession::create([
            'token'        => $token,
            'doctor_id'    => $doctor ? $user->id : null,
            'secretary_id' => $secretary ? $user->id : null,
            'step'         => 2,
            'expires_at'   => now()->addMinutes(10),
        ]);

        // ارسال پیامک
        $messagesService = new MessageService(
    SmsService::create(100253, $user->mobile, [$otpCode])
);
$response = $messagesService->send();
\Log::info('SMS send response', ['response' => $response]);
        session(['current_step' => 2, 'otp_token' => $token]);
        $this->dispatch('otpSent', token: $token);
        $this->redirect(route('dr.auth.login-confirm-form', ['token' => $token]), navigate: true);
    }

    public function render()
    {
        return view('livewire.dr.auth.doctor-login-register')
            ->layout('dr.layouts.dr-auth');
    }
}
