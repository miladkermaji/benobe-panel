<?php

namespace App\Livewire\Admin\Auth;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\LoginLog;
use App\Models\Admin\Manager;
use App\Models\LoginSession;
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
            \Log::info('Mount: Invalid or expired token: ' . $token);
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
        } else {
            \Log::info('Mount: Valid token: ' . $token);
        }
    }

    public function goBack()
    {
        $this->redirect(route('admin.auth.login-user-pass-form'), navigate: true);
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

    public function twoFactorCheck()
    {
        $this->validate();

        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 3)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            $this->addError('twoFactorSecret', 'دسترسی غیرمجاز یا توکن منقضی شده است. لطفاً دوباره وارد شوید.');
            \Log::info('Redirecting to login due to invalid session');
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

        if ($loginAttempts->isLocked($user->mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($user->mobile);
            $formattedTime = $this->formatTime($remainingTime);
            $this->addError('twoFactorSecret', "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید."); // خطا زیر اینپوت
            $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime); // SweetAlert
            \Log::info('Rate limit exceeded, remaining time: ' . $remainingTime);
            return;
        }

        \Log::info('Input: ' . $this->twoFactorSecret);
        \Log::info('Stored two_factor_secret: ' . $user->two_factor_secret);
        \Log::info('Hash check result: ' . (Hash::check($this->twoFactorSecret, $user->two_factor_secret) ? 'true' : 'false'));

        if (!$user->two_factor_secret || !Hash::check($this->twoFactorSecret, $user->two_factor_secret)) {
            $loginAttempts->incrementLoginAttempt($user->id, $user->mobile, '', '', $user->id);
            $this->addError('twoFactorSecret', 'کد دو عاملی وارد شده صحیح نیست.');
            \Log::info('Invalid two-factor code');
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
        \Log::info('Login successful, redirecting to admin panel');
        $this->redirect(route('admin-panel'));
    }

    public function render()
    {
        return view('livewire.admin.auth.two-factor')
            ->layout('admin.layouts.admin-auth');
    }
}