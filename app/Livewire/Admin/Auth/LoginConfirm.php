<?php

namespace App\Livewire\Admin\Auth;

use Carbon\Carbon;
use App\Models\Otp;
use Livewire\Component;
use App\Models\LoginLog;
use App\Models\LoginSession;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class LoginConfirm extends Component
{
    public $token;
    public $otpCode = ['', '', '', ''];
    public $remainingTime;
    public $showResendButton = false;

    public function mount($token)
    {
        $this->token = $token;
        $loginSession = LoginSession::where('token', $token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            session(['current_step' => 1]);
            $this->dispatch('otpExpired');
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $token)->first();
        $this->remainingTime = $otp
            ? max(0, (int) ($otp->created_at->addMinutes(2)->timestamp * 1000 - now()->timestamp * 1000))
            : 0;
        $this->showResendButton = $this->remainingTime <= 0;
    }

    public function goBack()
    {
        session(['current_step' => 1]);
        $this->dispatch('navigateTo', url: route('admin.auth.login-register-form'));
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

    public function loginConfirm()
    {
        $otpCode = strrev(implode('', $this->otpCode));
        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            $this->addError('otpCode', 'توکن منقضی شده یا نامعتبر است.');
            $this->dispatch('otpExpired');
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $this->token)
            ->where('used', 0)
            ->where('created_at', '>=', Carbon::now()->subMinutes(2))
            ->first();

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp?->manager?->mobile ?? $otp?->login_id ?? 'unknown';

        if ($loginAttempts->isLocked($mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
            $formattedTime = $this->formatTime($remainingTime);
            $this->addError('otpCode', "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.");
            $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime);
            return;
        }

        if (!$otp) {
            $this->addError('otpCode', 'کد تأیید نامعتبر یا منقضی شده است.');
            return;
        }

        if ($otp->otp_code !== $otpCode) {
            $userId = $otp->manager_id ?? null;
            $loginAttempts->incrementLoginAttempt($userId, $mobile, '', '', $userId);
            $this->addError('otpCode', 'کد تأیید وارد شده صحیح نیست.');
            return;
        }

        $otp->update(['used' => 1]);
        $user = $otp->manager;

        if (empty($user->mobile_verified_at)) {
            $user->update(['mobile_verified_at' => Carbon::now()]);
        }

        Auth::guard('manager')->login($user);
        $loginAttempts->resetLoginAttempts($user->mobile);
        session()->forget(['step1_completed', 'current_step', 'otp_token']);
        LoginSession::where('token', $this->token)->delete();

        LoginLog::create([
            'manager_id' => $user->id,
            'user_type' => 'manager',
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'device' => request()->header('User-Agent'),
        ]);

        $this->dispatch('loginSuccess');
        $this->redirect(route('admin-panel'));
    }

    public function resendOtp()
    {
        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            $this->dispatch('otpExpired');
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $this->token)->first();
        if (!$otp) {
            $this->dispatch('otpExpired');
            $this->redirect(route('admin.auth.login-register-form'), navigate: true);
            return;
        }

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp->manager?->mobile ?? $otp->login_id ?? 'unknown';

        if ($loginAttempts->isLocked($mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
            $formattedTime = $this->formatTime($remainingTime);
            $this->addError('otpCode', "شما بیش از حد تلاش کرده‌اید. لطفاً $formattedTime صبر کنید.");
            $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime);
            return;
        }

        $otpCode = rand(1000, 9999);
        $newToken = Str::random(60);

        Otp::create([
            'token' => $newToken,
            'manager_id' => $otp->manager_id,
            'otp_code' => $otpCode,
            'login_id' => $otp->manager->mobile,
            'type' => 0,
        ]);

        LoginSession::where('token', $this->token)->delete();
        LoginSession::create([
            'token' => $newToken,
            'manager_id' => $otp->manager_id,
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $messagesService = new MessageService(
            SmsService::create(100253, $otp->manager->mobile, [$otpCode])
        );
        $messagesService->send();
        $this->token = $newToken;
        $this->remainingTime = 120000; // 2 دقیقه
        $this->showResendButton = false;
        $this->otpCode = ['', '', '', ''];
        $countDownDate = now()->addMinutes(2)->timestamp * 1000; // زمان پایان جدید
        session(['otp_token' => $newToken]);

        $this->dispatch('otpResent', [
            'message' => 'کد جدید ارسال شد',
            'remainingTime' => $this->remainingTime,
            'countDownDate' => $countDownDate,
            'showResendButton' => false,
        ]);
    
    }

    // تابع جدید برای به‌روزرسانی تایمر
    public function updateTimer()
    {
        $otp = Otp::where('token', $this->token)->first();
        if ($otp) {
            $this->remainingTime = max(0, (int) ($otp->created_at->addMinutes(2)->timestamp - now()->timestamp) * 1000);
            $this->showResendButton = $this->remainingTime <= 0;
        }
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'remainingTime' && $this->remainingTime <= 0 && !$this->showResendButton) {
            $this->showResendButton = true;
            $this->dispatch('updateShowResendButton', ['show' => true]);
        }
    }

    public function render()
    {
        $otp = Otp::where('token', $this->token)->first();
        $countDownDate = $otp ? $otp->created_at->addMinutes(2)->timestamp * 1000 : 0;
        $this->remainingTime = $otp ? max(0, (int) ($countDownDate - now()->timestamp * 1000)) : 0;
        $this->showResendButton = $this->remainingTime <= 0;

        $this->dispatch('initOtpForm', [
            'remainingTime' => $this->remainingTime,
            'countDownDate' => $countDownDate,
            'showResendButton' => $this->showResendButton,
        ]);

        return view('livewire.admin.auth.login-confirm', [
            'remainingTime' => $this->remainingTime,
            'countDownDate' => $countDownDate,
            'showResendButton' => $this->showResendButton,
        ])->layout('admin.layouts.admin-auth');
    }
}