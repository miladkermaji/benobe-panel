<?php

namespace App\Livewire\Admin\Auth;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\LoginLog;
use App\Models\LoginSession;
use App\Models\Admin\Manager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class TwoFactor extends Component
{
    public $twoFactorSecret;
    public $token;

    protected $rules = [
        'twoFactorSecret' => 'required|string|min:6',
    ];

    protected $messages = [
        'twoFactorSecret.required' => 'لطفاً کد دو عاملی را وارد کنید.',
        'twoFactorSecret.min' => 'کد دو عاملی باید حداقل 6 کاراکتر باشد.',
    ];

    public function mount($token)
    {
        $this->token = $token;
        $loginSession = LoginSession::where('token', $token)
            ->where('step', 3)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
        }
    }

    public function goBack()
    {
        $this->redirect(route('admin.auth.login-user-pass-form'), navigate: true);
    }

    // تابع جدید برای فرمت کردن زمان مشابه سیستم OTP
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

    public function twoFactorCheck()
    {
        $this->validate();

        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 3)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            $this->addError('twoFactorSecret', 'دسترسی غیرمجاز یا توکن منقضی شده است. لطفاً دوباره وارد شوید.');
            Log::info('Redirecting to login due to invalid session');
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
            return;
        }

        $loginAttempts = new LoginAttemptsService();
        $user = Manager::where('id', $loginSession->manager_id)->first();

        if (!$user) {
            $this->addError('twoFactorSecret', 'کاربر یافت نشد.');
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
            return;
        }

        // بررسی قفل بودن حساب
        if ($loginAttempts->isLocked($user->mobile)) {
            $this->dispatch('rateLimitExceeded', remainingTime: $loginAttempts->getRemainingLockTime($user->mobile));
            Log::info('Rate limit exceeded, remaining time: ' . $loginAttempts->getRemainingLockTime($user->mobile));
            return;
        }

        if (!$user->two_factor_secret || !Hash::check($this->twoFactorSecret, $user->two_factor_secret)) {
            $loginAttempts->incrementLoginAttempt($user->id, $user->mobile, null, null, $user->id);
            $this->addError('twoFactorSecret', 'کد دو عاملی وارد شده صحیح نیست.');
            return;
        }

        $user->update(['two_factor_confirmed_at' => Carbon::now()]);
        Auth::guard('manager')->login($user);

        LoginLog::create([
            'manager_id' => $user->id,
            'user_type' => 'manager',
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'device' => request()->header('User-Agent'),
            'login_method' => 'two_factor',
        ]);

        $loginAttempts->resetLoginAttempts($user->mobile);
        LoginSession::where('token', $this->token)->delete();
        $this->dispatch('loginSuccess');
        $this->redirect(route('admin-panel'));
    }

    public function render()
    {
        return view('livewire.admin.auth.two-factor')
            ->layout('admin.layouts.admin-auth');
    }
}
