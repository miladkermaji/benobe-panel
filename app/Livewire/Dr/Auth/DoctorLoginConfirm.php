<?php

namespace App\Livewire\Dr\Auth;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\Doctor;
use Livewire\Component;
use App\Models\LoginLog;
use App\Models\Secretary;
use Illuminate\Support\Str;
use App\Models\LoginSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use App\Http\Services\LoginAttemptsService\LoginAttemptsService;

class DoctorLoginConfirm extends Component
{
    public $token;
    public $otpCode = ['', '', '', ''];
    public $remainingTime;
    public $countDownDate;
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
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $token)->first();
        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp?->doctor?->mobile ?? $otp?->secretary?->mobile ?? $otp->login_id ?? 'unknown';

        if ($otp) {
            $this->countDownDate = $otp->created_at->addMinutes(2)->timestamp * 1000;
            $this->remainingTime = max(0, $this->countDownDate - now()->timestamp * 1000);
            $this->showResendButton = $this->remainingTime <= 0 && !$loginAttempts->isLocked($mobile);
        } else {
            $this->remainingTime = 0;
            $this->countDownDate = 0;
            $this->showResendButton = !$loginAttempts->isLocked($mobile);
        }

        $this->dispatch('initTimer', [
            'remainingTime' => $this->remainingTime,
            'countDownDate' => $this->countDownDate,
            'showResendButton' => $this->showResendButton,
            'token' => $this->token,
        ]);
    }

    public function goBack()
    {
        session(['current_step' => 1, 'from_back' => true]);

        $this->dispatch('navigateTo', url: route('dr.auth.login-register-form'));
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
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $this->token)
            ->where('used', 0)
            ->where('created_at', '>=', Carbon::now()->subMinutes(2))
            ->first();

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp?->doctor?->mobile ?? $otp?->secretary?->mobile ?? $otp->login_id ?? 'unknown';

        if ($loginAttempts->isLocked($mobile)) {
            $this->dispatch('rateLimitExceeded', remainingTime: $loginAttempts->getRemainingLockTime($mobile));
            return;
        }

        if (!$otp) {
            $this->addError('otpCode', 'کد تأیید نامعتبر یا منقضی شده است.');
            $this->showResendButton = !$loginAttempts->isLocked($mobile);
            return;
        }

        if ($otp->otp_code !== $otpCode) {
            $userId = $otp->doctor_id ?? $otp->secretary_id ?? null;
            $loginAttempts->incrementLoginAttempt(
                $userId,
                $mobile,
                $otp->doctor_id,
                $otp->secretary_id,
                null
            );
            $this->addError('otpCode', 'کد تأیید وارد شده صحیح نیست.');
            return;
        }

        $otp->update(['used' => 1]);
        $user = $otp->doctor ?? $otp->secretary;

        if (empty($user->mobile_verified_at)) {
            $user->update(['mobile_verified_at' => Carbon::now()]);
        }

        if ($user instanceof Doctor) {
            Auth::guard('doctor')->login($user);
            $redirectRoute = route('dr-panel');
            $userType = 'doctor';
        } else {
            Auth::guard('secretary')->login($user);
            $redirectRoute = route('dr-panel');
            $userType = 'secretary';
        }

        Log::info("Resetting login attempts for mobile: $mobile");
        $loginAttempts->resetLoginAttempts($user->mobile);
        session()->forget(['step1_completed', 'current_step', 'otp_token']);
        LoginSession::where('token', $this->token)->delete();

        LoginLog::create([
            'doctor_id' => $user instanceof Doctor ? $user->id : null,
            'secretary_id' => $user instanceof Secretary ? $user->id : null,
            'user_type' => $userType,
            'login_at' => now(),
            'ip_address' => request()->ip(),
            'device' => request()->header('User-Agent'),
        ]);

        $this->dispatch('loginSuccess');
        $this->redirect($redirectRoute);
    }

    public function resendOtp()
    {
        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            $this->dispatch('otpExpired');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $otp = Otp::where('token', $this->token)->first();
        if (!$otp) {
            $this->dispatch('otpExpired');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        $loginAttempts = new LoginAttemptsService();
        $mobile = $otp->doctor?->mobile ?? $otp->secretary?->mobile ?? $otp->login_id ?? 'unknown';

        // بررسی قفل بودن حساب
        if ($loginAttempts->isLocked($mobile)) {
            $remainingTime = $loginAttempts->getRemainingLockTime($mobile);
            if ($remainingTime > 0) {
                $this->dispatch('rateLimitExceeded', remainingTime: $remainingTime);
                return;
            }
        }

        $otpCode = rand(1000, 9999);
        $newToken = Str::random(60);

        Otp::create([
            'token' => $newToken,
            'doctor_id' => $otp->doctor_id,
            'secretary_id' => $otp->secretary_id,
            'otp_code' => $otpCode,
            'login_id' => $mobile,
            'type' => 0,
        ]);

        LoginSession::where('token', $this->token)->delete();
        LoginSession::create([
            'token' => $newToken,
            'doctor_id' => $otp->doctor_id,
            'secretary_id' => $otp->secretary_id,
            'step' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $user = $otp->doctor ?? $otp->secretary;
        $messagesService = new MessageService(
            SmsService::create(100286, $user->mobile, [$otpCode])
        );
        $messagesService->send();

        $this->token = $newToken;
        $this->remainingTime = 120000;
        $this->countDownDate = now()->addMinutes(2)->timestamp * 1000;
        $this->showResendButton = false;
        $this->otpCode = ['', '', '', ''];
        session(['otp_token' => $newToken]);

        $this->dispatch(
            'otpResent',
            message: 'کد جدید ارسال شد',
            remainingTime: $this->remainingTime,
            countDownDate: $this->countDownDate,
            showResendButton: false,
            token: $newToken,
        );
    }

    public function render()
    {
        $loginSession = LoginSession::where('token', $this->token)
            ->where('step', 2)
            ->where('expires_at', '>', now())
            ->first();

        if (!$loginSession) {
            session(['current_step' => 1]);
            $this->dispatch('otpExpired');
            $this->redirect(route('dr.auth.login-register-form'), navigate: true);
            return;
        }

        return view('livewire.dr.auth.doctor-login-confirm', [
            'remainingTime' => $this->remainingTime,
            'countDownDate' => $this->countDownDate,
            'showResendButton' => $this->showResendButton,
        ])->layout('dr.layouts.dr-auth');
    }
}
