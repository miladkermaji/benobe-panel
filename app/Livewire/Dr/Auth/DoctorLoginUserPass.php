<?php

namespace App\Livewire\Dr\Auth;

use App\Http\Services\LoginAttemptsService\LoginAttemptsService;
use App\Models\Doctor;
use App\Models\LoginLog;
use App\Models\LoginSession;
use App\Models\Secretary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class DoctorLoginUserPass extends Component
{
    public $mobile;
    public $password;

    protected $rules = [
        'mobile' => [
            'required',
            'string',
            'regex:/^(?!09{1}(\d)\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)\d{7}$/',
        ],
        'password' => 'required|string|min:6',
    ];

    protected $messages = [
        'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
        'mobile.regex' => 'شماره موبایل باید فرمت معتبر داشته باشد (مثلاً 09181234567).',
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

        $mobile = preg_replace('/^(\+98|98|0)/', '', $this->mobile);
        $formattedMobile = '0' . $mobile;
        $loginAttempts = new LoginAttemptsService();

        // بررسی قفل بودن حساب
        if ($loginAttempts->isLocked($formattedMobile)) {
            $this->dispatch('rateLimitExceeded', remainingTime: $loginAttempts->getRemainingLockTime($formattedMobile));
            return;
        }

        $doctor = Doctor::where('mobile', $formattedMobile)->first();
        $secretary = Secretary::where('mobile', $formattedMobile)->first();

        if (!$doctor && !$secretary) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, null, null, null);
            $this->addError('mobile', 'کاربری با این شماره موبایل وجود ندارد.');
            return;
        }

        $user = $doctor ?? $secretary;

        // بررسی فعال بودن قابلیت ورود با رمز عبور
        if (($user->static_password_enabled ?? 0) !== 1) {
            $loginAttempts->incrementLoginAttempt(
                $user->id,
                $formattedMobile,
                $doctor ? $doctor->id : null,
                $secretary ? $secretary->id : null,
                null
            );
            $this->addError('mobile', 'شما قابلیت ورود با رمز عبور را فعال نکرده‌اید.');
            return;
        }

        // بررسی رمز عبور و وضعیت حساب
        if (!Hash::check($this->password, $user->password) || $user->status !== 1) {
            $loginAttempts->incrementLoginAttempt(
                $user->id,
                $formattedMobile,
                $doctor ? $doctor->id : null,
                $secretary ? $secretary->id : null,
                null
            );
            $this->addError('mobile', 'شماره موبایل یا رمز عبور نادرست است یا حساب غیرفعال است.');
            return;
        }

        // ایجاد توکن برای جلسه ورود
        $token = Str::random(60);
        LoginSession::create([
            'token' => $token,
            'doctor_id' => $doctor ? $user->id : null,
            'secretary_id' => $secretary ? $user->id : null,
            'step' => 3,
            'expires_at' => now()->addMinutes(10),
        ]);

        // بررسی احراز هویت دو مرحله‌ای
        if (($user->two_factor_enabled ?? 0) == 1) {
            return $this->redirect(route('dr-two-factor', ['token' => $token]), navigate: true);
        }

        // ورود با گارد مناسب
        if ($user instanceof Doctor) {
            Auth::guard('doctor')->login($user);
            $redirectRoute = route('dr-panel');
            $userType = 'doctor';
        } else {
            Auth::guard('secretary')->login($user);
            $redirectRoute = route('dr-panel');
            $userType = 'secretary';
        }

        // ثبت لاگ ورود
        LoginLog::create([
            'doctor_id' => $user instanceof Doctor ? $user->id : null,
            'secretary_id' => $user instanceof Secretary ? $user->id : null,
            'user_type' => $userType,
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'device' => request()->header('User-Agent'),
            'login_method' => 'password',
        ]);

        // ریست کردن تعداد تلاش‌ها پس از ورود موفق
        $loginAttempts->resetLoginAttempts($formattedMobile);
        LoginSession::where('doctor_id', $user->id)->orWhere('secretary_id', $user->id)->delete();
        $this->dispatch('loginSuccess');
        $this->redirect($redirectRoute);
    }

    public function render()
    {
        return view('livewire.dr.auth.doctor-login-user-pass')->layout('dr.layouts.dr-auth');
    }
}
