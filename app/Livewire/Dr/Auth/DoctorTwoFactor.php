<?php
namespace App\Livewire\Dr\Auth;

use App\Http\Services\LoginAttemptsService\LoginAttemptsService;
use App\Models\Doctor;
use App\Models\Dr\Secretary;
use App\Models\LoginLog;
use App\Models\LoginSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class DoctorTwoFactor extends Component
{
    public $twoFactorSecret;
    public $token;

    protected $rules = [
        'twoFactorSecret' => 'required|string|min:6',
    ];

    protected $messages = [
        'twoFactorSecret.required' => 'لطفاً کد دو عاملی را وارد کنید.',
        'twoFactorSecret.min'      => 'کد دو عاملی باید حداقل 6 کاراکتر باشد.',
    ];

    public function mount($token)
    {
        $this->token  = $token;
        $loginSession = LoginSession::where('token', $token)
            ->where('step', 3)
            ->where('expires_at', '>', now())
            ->first();

        if (! $loginSession) {
            \Log::info('Mount: Invalid or expired token: ' . $token);
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
        } else {
            \Log::info('Mount: Valid token: ' . $token);
        }
    }

    public function goBack()
    {
        $this->redirect(route('dr.auth.login-user-pass-form'), navigate: true);
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

    public function twoFactorCheck()
    {
        $this->validate();

        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 3)
            ->where('expires_at', '>', now())
            ->first();

        if (! $loginSession) {
            $this->addError('twoFactorSecret', 'دسترسی غیرمجاز یا توکن منقضی شده است. لطفاً دوباره وارد شوید.');
            \Log::info('Redirecting to login due to invalid session');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $loginAttempts = new LoginAttemptsService();
        $user          = $loginSession->doctor_id
        ? Doctor::where('id', $loginSession->doctor_id)->first()
        : Secretary::where('id', $loginSession->secretary_id)->first();

        if (! $user) {
            $this->addError('twoFactorSecret', 'کاربر یافت نشد.');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        if ($loginAttempts->isLocked($user->mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($user->mobile);
            $formattedTime = $this->formatTime($remainingTime);
            $this->addError('twoFactorSecret', "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.");
            $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime);
            \Log::info('Rate limit exceeded, remaining time: ' . $remainingTime);
            return;
        }

        \Log::info('Input: ' . $this->twoFactorSecret);
        \Log::info('Stored two_factor_secret: ' . $user->two_factor_secret);
        \Log::info('Hash check result: ' . (Hash::check($this->twoFactorSecret, $user->two_factor_secret) ? 'true' : 'false'));

        if (! $user->two_factor_secret || ! Hash::check($this->twoFactorSecret, $user->two_factor_secret)) {
            $loginAttempts->incrementLoginAttempt(
                $user->id,
                $user->mobile,
                $user instanceof Doctor ? $user->id : null,
                $user instanceof Secretary ? $user->id : null,
                null
            );
            $this->addError('twoFactorSecret', 'کد دو عاملی وارد شده صحیح نیست.');
            \Log::info('Invalid two-factor code');
            return;
        }

        $user->update(['two_factor_confirmed_at' => Carbon::now()]);

        // ورود با گارد مناسب
        if ($user instanceof Doctor) {
            Auth::guard('doctor')->login($user);
            $redirectRoute = route('dr-panel');
            $userType      = 'doctor';
        } else {
            Auth::guard('secretary')->login($user);
            $redirectRoute = route('dr-panel'); // فرض می‌کنم پنل منشی dr-panel باشه
            $userType      = 'secretary';
        }

        LoginLog::create([
            'doctor_id'    => $user instanceof Doctor ? $user->id : null,
            'secretary_id' => $user instanceof Secretary ? $user->id : null,
            'user_type'    => $userType,
            'login_at'     => now(),
            'ip_address'   => request()->ip(),
            'device'       => request()->header('User-Agent'),
            'login_method' => 'two_factor',
        ]);

        $loginAttempts->resetLoginAttempts($user->mobile);
        LoginSession::where('token', $this->token)->delete();
        $this->dispatch('loginSuccess');
        \Log::info('Login successful, redirecting to panel');
        $this->redirect($redirectRoute);
    }

    public function render()
    {
        return view('livewire.dr.auth.doctor-two-factor')
            ->layout('dr.layouts.dr-auth');
    }
}
