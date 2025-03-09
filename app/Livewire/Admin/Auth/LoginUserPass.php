<?php

namespace App\Livewire\Admin\Auth;

use Livewire\Component;
use App\Models\LoginLog;
use App\Models\Admin\Manager;
use App\Models\LoginSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class LoginUserPass extends Component
{
    public $mobile;
    public $password;

    protected $rules = [
        'mobile' => [
            'required',
            'string',
            'regex:/^(?!09{1}(\d)\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)\d{7}$/'
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
        if (Auth::guard('manager')->check()) {
            $this->redirect(route('admin-panel'));
        }
    }

    public function goBack()
    {
        $this->redirect(route('admin.auth.login-register-form'), navigate: true);
    }

    // تابع جدید برای فرمت کردن زمان
    private function formatTime($seconds)
    {
        if (is_null($seconds) || $seconds < 0) {
            return '0 دقیقه و 0 ثانیه';
        }
        $minutes = floor($seconds / 60);
        $remainingSeconds = round($seconds % 60);
        return "$minutes دقیقه و $remainingSeconds ثانیه";
    }

    public function loginWithMobilePass()
    {
        $this->validate();

        $mobile = preg_replace('/^(\+98|98|0)/', '', $this->mobile);
        $formattedMobile = '0' . $mobile;
        $loginAttempts = new LoginAttemptsService();

        if ($loginAttempts->isLocked($formattedMobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($formattedMobile);
            $formattedTime = $this->formatTime($remainingTime); // فرمت کردن زمان
            $this->addError('mobile', "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.");
            $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime);
            return;
        }

        $manager = Manager::where('mobile', $formattedMobile)->first();

        if (!$manager) {
            $loginAttempts->incrementLoginAttempt(null, $formattedMobile, '', '', null);
            $this->addError('mobile', 'کاربری با این شماره موبایل وجود ندارد.');
            return;
        }

        if ($manager->static_password_enabled !== 1) {
            $loginAttempts->incrementLoginAttempt($manager->id, $formattedMobile, '', '', $manager->id);
            $this->addError('mobile', 'شما قابلیت ورود با رمز عبور را فعال نکرده‌اید.');
            return;
        }

        if (!Hash::check($this->password, $manager->password) || $manager->status !== 1) {
            $loginAttempts->incrementLoginAttempt($manager->id, $formattedMobile, '', '', $manager->id);
            $this->addError('mobile', 'شماره موبایل یا رمز عبور نادرست است یا حساب غیرفعال است.');
            return;
        }

        $token = Str::random(60);
        LoginSession::create([
            'token' => $token,
            'manager_id' => $manager->id,
            'step' => 3,
            'expires_at' => now()->addMinutes(10),
        ]);

        if ($manager->two_factor_enabled == 1) {
            return $this->redirect(route('admin-two-factor', ['token' => $token]), navigate: true);
        }

        Auth::guard('manager')->login($manager);
        LoginLog::create([
            'manager_id' => $manager->id,
            'user_type' => 'manager',
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'device' => request()->header('User-Agent'),
            'login_method' => 'password',
        ]);

        $loginAttempts->resetLoginAttempts($formattedMobile);
        LoginSession::where('manager_id', $manager->id)->delete();
        $this->dispatch('loginSuccess');
        $this->redirect(route('admin-panel'));
    }

    public function render()
    {
        return view('livewire.admin.auth.login-user-pass')->layout('admin.layouts.admin-auth');
    }
}